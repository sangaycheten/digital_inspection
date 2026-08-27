<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Client;
use App\Models\Job;
use App\Models\MasterLookup;
use App\Models\Site;
use App\Models\User;
use App\Mail\InspectionCertificateMail;
use App\Notifications\JobAssignedNotification;
use App\Models\InspectionRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $jobs = Job::with(['site', 'client', 'technicians'])
            ->when($request->search, fn ($q) =>
                $q->where('scope_notes', 'like', "%{$request->search}%")
            )
            ->when($request->site_id,    fn ($q) => $q->where('site_id', $request->site_id))
            ->when($request->client_id,  fn ($q) => $q->where('client_id', $request->client_id))
            ->when($request->work_type,  fn ($q) => $q->where('work_type', $request->work_type))
            ->when($request->status,     fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $sites   = Site::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();

        return view('admin.jobs.index', compact('jobs', 'sites', 'clients'));
    }

    public function create(): View
    {
        $sites       = Site::with('client')->orderBy('name')->get();
        $technicians = User::role('field-technician')->with('sites')->orderBy('name')->get();

        $firstInspectedBuildingIds = $this->completedFirstInspectionBuildingIds();

        return view('admin.jobs.create', compact('sites', 'technicians', 'firstInspectedBuildingIds'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_id'         => ['required', 'exists:sites,id'],
            'client_id'       => ['required', 'exists:clients,id'],
            'work_type'       => ['required', 'in:' . implode(',', array_keys(Job::WORK_TYPES))],
            'scheduled_date'  => ['nullable', 'date'],
            'scope_notes'     => ['nullable', 'string'],
            'assignments'     => ['required', 'array', 'min:1'],
            'assignments.*'   => ['nullable', 'array'],
            'assignments.*.*' => ['exists:users,id'],
        ], [
            'assignments.required' => 'Assign at least one technician to a building.',
            'assignments.min'      => 'Assign at least one technician to a building.',
        ]);

        if (!$this->hasAnyAssignment($data['assignments'])) {
            return back()->withInput()->withErrors(['assignments' => 'Assign at least one technician to a building.']);
        }

        if ($data['work_type'] === 'first_inspection') {
            $selectedBuildingIds = collect($data['assignments'])->keys()->filter()->values()->all();
            $blocked = $this->completedFirstInspectionBuildingIds(buildingIds: $selectedBuildingIds);
            if ($blocked->isNotEmpty()) {
                return back()->withInput()->withErrors([
                    'assignments' => 'One or more selected buildings have already had a completed First Inspection and cannot be assigned again.',
                ]);
            }
        }

        $job = Job::create([
            'site_id'        => $data['site_id'],
            'client_id'      => $data['client_id'],
            'work_type'      => $data['work_type'],
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'scope_notes'    => $data['scope_notes'] ?? null,
            'created_by'     => Auth::id(),
        ]);

        $this->syncAssignments($job, $data['assignments']);
        $this->notifyAssignedTechnicians($job, $data['assignments']);

        return redirect()->route('admin.jobs.show', $job)
            ->with('success', 'Job created successfully.');
    }

    public function show(Job $job): View
    {
        $job->load([
            'site.client',
            'client',
            'creator',
            'technicians',
            'buildings',
            'targetAssets.asset',
            'installationAssets.asset',
            'inspectionRecords.asset',
        ]);

        $assetTypes  = MasterLookup::assetTypeMap();
        $assignments = $this->loadAssignmentMatrix($job->id);

        return view('admin.jobs.show', compact('job', 'assetTypes', 'assignments'));
    }

    public function edit(Job $job): View
    {
        $technicians = User::role('field-technician')->with('sites')->orderBy('name')->get();
        $buildings   = Building::where('site_id', $job->site_id)->orderBy('name_or_level')->get();

        $assignments = DB::table('job_technician_buildings')
            ->where('job_id', $job->id)
            ->get()
            ->groupBy('building_id')
            ->map(fn ($rows) => $rows->pluck('user_id')->toArray());

        $firstInspectedBuildingIds = $this->completedFirstInspectionBuildingIds(excludeJobId: $job->id);

        return view('admin.jobs.edit', compact('job', 'technicians', 'buildings', 'assignments', 'firstInspectedBuildingIds'));
    }

    public function update(Request $request, Job $job): RedirectResponse
    {
        $data = $request->validate([
            'work_type'       => ['required', 'in:' . implode(',', array_keys(Job::WORK_TYPES))],
            'status'          => ['required', 'in:' . implode(',', array_keys(Job::STATUSES))],
            'scheduled_date'  => ['nullable', 'date'],
            'scope_notes'     => ['nullable', 'string'],
            'assignments'     => ['required', 'array', 'min:1'],
            'assignments.*'   => ['nullable', 'array'],
            'assignments.*.*' => ['exists:users,id'],
        ], [
            'assignments.required' => 'Assign at least one technician to a building.',
            'assignments.min'      => 'Assign at least one technician to a building.',
        ]);

        if (!$this->hasAnyAssignment($data['assignments'])) {
            return back()->withInput()->withErrors(['assignments' => 'Assign at least one technician to a building.']);
        }

        if ($data['work_type'] === 'first_inspection') {
            $selectedBuildingIds = collect($data['assignments'])->keys()->filter()->values()->all();
            $blocked = $this->completedFirstInspectionBuildingIds(buildingIds: $selectedBuildingIds, excludeJobId: $job->id);
            if ($blocked->isNotEmpty()) {
                return back()->withInput()->withErrors([
                    'assignments' => 'One or more selected buildings have already had a completed First Inspection and cannot be assigned again.',
                ]);
            }
        }

        $nextStatuses = $job->nextStatuses();
        if ($data['status'] !== $job->status && !in_array($data['status'], $nextStatuses)) {
            return back()->withInput()->withErrors([
                'status' => "Cannot transition from '{$job->status}' to '{$data['status']}'.",
            ]);
        }

        $previousStatus        = $job->status;
        $previousTechnicianIds = $job->technicians()->pluck('users.id')->all();

        $job->update([
            'work_type'      => $data['work_type'],
            'status'         => $data['status'],
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'scope_notes'    => $data['scope_notes'] ?? null,
        ]);

        $this->syncAssignments($job, $data['assignments']);
        $this->notifyAssignedTechnicians($job, $data['assignments'], $previousTechnicianIds);

        // Auto-generate certificate when status advances to "issued"
        if ($previousStatus !== 'issued' && $data['status'] === 'issued') {
            return redirect()->route('admin.jobs.show', $job)
                ->with('success', 'Job marked as Issued. The inspection certificate is ready.')
                ->with('certificate_ready', $job->id);
        }

        return redirect()->route('admin.jobs.show', $job)
            ->with('success', 'Job updated successfully.');
    }

    public function sendCertificate(Job $job): RedirectResponse
    {
        abort_if(!in_array($job->status, ['issued', 'closed']), 403, 'Certificate only available after the job is issued.');

        $job->load(['client.manager', 'site', 'technicians', 'buildings']);

        // Collect all unique valid recipient emails
        $recipients = collect();

        // 1. Client primary email field
        $clientEmail = trim($job->client->email ?? '');
        if (filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            $recipients->push($clientEmail);
        }

        // 2. Client portal users
        \App\Models\User::where('client_id', $job->client_id)
            ->whereNotNull('email')
            ->pluck('email')
            ->each(fn ($e) => $recipients->contains($e) ?: $recipients->push($e));

        // 3. billing_contact_info if it is a standalone valid email
        $billingEmail = trim($job->client->billing_contact_info ?? '');
        if (filter_var($billingEmail, FILTER_VALIDATE_EMAIL) && !$recipients->contains($billingEmail)) {
            $recipients->push($billingEmail);
        }

        if ($recipients->isEmpty()) {
            return back()->with('error', 'No valid email address found for this client. Add an email to the client profile or create a client user with an email.');
        }

        foreach ($recipients as $email) {
            Mail::to($email)->send(new InspectionCertificateMail($job));
        }

        $job->update(['certificate_sent_at' => now()]);

        return back()->with('success', 'Certificate emailed successfully to the client.');
    }

    public function toggleCertificateAccess(Job $job): RedirectResponse
    {
        abort_if(!in_array($job->status, ['issued', 'closed']), 403, 'Certificate only available after the job is issued.');

        $job->update(['certificate_accessible' => !$job->certificate_accessible]);

        $msg = $job->certificate_accessible
            ? 'Client portal access enabled — client can now download the certificate.'
            : 'Client portal access revoked.';

        return back()->with('success', $msg);
    }

    public function certificate(Job $job): Response
    {
        $user = Auth::user();
        $isAdmin    = $user->hasRole('system-administrator');
        $isManager  = $user->hasRole('manager');
        $isClient   = $user->hasRole('client-user');

        abort_if(!in_array($job->status, ['issued', 'closed']), 403, 'Certificate only available after the job is issued.');
        abort_if($isClient && (!$job->certificate_accessible || $user->client_id !== $job->client_id), 403, 'Certificate is not yet available.');

        $job->load([
            'client.manager',
            'site',
            'technicians',
            'buildings',
        ]);

        $records = InspectionRecord::with(['asset.building'])
            ->where('job_id', $job->id)
            ->where('document_status', 'approved')
            ->orderBy('created_at')
            ->get()
            ->groupBy('asset_id')
            ->map(fn ($recs) => $recs->sortByDesc('created_at')->first())
            ->values();

        $assetTypes = \App\Models\MasterLookup::assetTypeMap();

        $pdf = Pdf::loadView('admin.jobs.certificate', compact('job', 'records', 'assetTypes'))
            ->setPaper('a4', 'portrait');

        $filename = 'inspection-certificate-' . strtoupper(substr($job->id, -8)) . '.pdf';

        return $pdf->download($filename);
    }

    private function hasAnyAssignment(array $assignments): bool
    {
        foreach ($assignments as $userIds) {
            if (!empty($userIds)) {
                return true;
            }
        }
        return false;
    }

    private function syncAssignments(Job $job, array $assignments): void
    {
        $now            = now();
        $allUserIds     = [];
        $allBuildingIds = [];
        $newRows        = [];

        // Load existing assignments keyed by "user_id|building_id" to preserve their created_at
        $existing = DB::table('job_technician_buildings')
            ->where('job_id', $job->id)
            ->get()
            ->keyBy(fn ($r) => $r->user_id . '|' . $r->building_id);

        foreach ($assignments as $buildingId => $userIds) {
            if (empty($userIds)) continue;
            $allBuildingIds[] = $buildingId;
            foreach ($userIds as $userId) {
                $allUserIds[] = $userId;
                $key = $userId . '|' . $buildingId;
                // Preserve original assigned_at for existing rows; set now() for new ones
                $newRows[] = [
                    'job_id'      => $job->id,
                    'building_id' => $buildingId,
                    'user_id'     => $userId,
                    'created_at'  => $existing->has($key) ? $existing[$key]->created_at : $now,
                ];
            }
        }

        // Replace all rows atomically, preserving original timestamps where the assignment already existed
        DB::table('job_technician_buildings')->where('job_id', $job->id)->delete();
        if (!empty($newRows)) {
            DB::table('job_technician_buildings')->insert($newRows);
        }

        $job->technicians()->sync(array_unique($allUserIds));
        $job->buildings()->sync(array_unique($allBuildingIds));
    }

    private function notifyAssignedTechnicians(Job $job, array $assignments, array $previousTechnicianIds = []): void
    {
        $job->loadMissing(['site', 'client']);

        // Group buildings by technician, skipping previously assigned technicians
        $techBuildings = [];
        foreach ($assignments as $buildingId => $userIds) {
            if (empty($userIds)) continue;
            foreach ($userIds as $userId) {
                if (in_array($userId, $previousTechnicianIds)) continue;
                $techBuildings[$userId][] = $buildingId;
            }
        }

        if (empty($techBuildings)) return;

        $buildingNameMap = Building::whereIn('id', collect($techBuildings)->flatten()->unique()->all())
            ->pluck('name_or_level', 'id');

        foreach ($techBuildings as $userId => $buildingIds) {
            $technician = User::find($userId);
            if (!$technician) continue;

            $buildingNames = collect($buildingIds)
                ->map(fn ($id) => $buildingNameMap->get($id))
                ->filter()->sort()->values()->all();

            $technician->notify(new JobAssignedNotification(
                job: $job,
                buildingNames: $buildingNames,
            ));
        }
    }

    private function completedFirstInspectionBuildingIds(array $buildingIds = [], ?string $excludeJobId = null): \Illuminate\Support\Collection
    {
        $query = DB::table('job_buildings')
            ->join('work_orders', 'work_orders.id', '=', 'job_buildings.job_id')
            ->where('work_orders.work_type', 'first_inspection')
            ->whereIn('work_orders.status', ['approved', 'issued', 'closed']);

        if (!empty($buildingIds)) {
            $query->whereIn('job_buildings.building_id', $buildingIds);
        }

        if ($excludeJobId) {
            $query->where('job_buildings.job_id', '!=', $excludeJobId);
        }

        return $query->pluck('job_buildings.building_id')->unique()->values();
    }

    private function loadAssignmentMatrix(string $jobId): \Illuminate\Support\Collection
    {
        return DB::table('job_technician_buildings')
            ->where('job_id', $jobId)
            ->join('users', 'users.id', '=', 'job_technician_buildings.user_id')
            ->join('buildings', 'buildings.id', '=', 'job_technician_buildings.building_id')
            ->select('buildings.name_or_level as building', 'users.name as technician')
            ->orderBy('buildings.name_or_level')
            ->orderBy('users.name')
            ->get()
            ->groupBy('building');
    }
}
