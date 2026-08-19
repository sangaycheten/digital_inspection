<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Client;
use App\Models\Job;
use App\Models\MasterLookup;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $sites        = Site::with('client')->orderBy('name')->get();
        $technicians  = User::role('field-technician')->orderBy('name')->get();

        return view('admin.jobs.create', compact('sites', 'technicians'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_id'        => ['required', 'exists:sites,id'],
            'client_id'      => ['required', 'exists:clients,id'],
            'work_type'      => ['required', 'in:' . implode(',', array_keys(Job::WORK_TYPES))],
            'scheduled_date' => ['nullable', 'date'],
            'scope_notes'    => ['nullable', 'string'],
            'technician_ids' => ['nullable', 'array'],
            'technician_ids.*' => ['exists:users,id'],
            'building_ids'   => ['nullable', 'array'],
            'building_ids.*' => ['exists:buildings,id'],
        ]);

        $job = Job::create([
            'site_id'        => $data['site_id'],
            'client_id'      => $data['client_id'],
            'work_type'      => $data['work_type'],
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'scope_notes'    => $data['scope_notes'] ?? null,
            'created_by'     => Auth::id(),
        ]);

        $job->technicians()->sync($data['technician_ids'] ?? []);
        $job->buildings()->sync($data['building_ids'] ?? []);

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

        $assetTypes = MasterLookup::assetTypeMap();

        return view('admin.jobs.show', compact('job', 'assetTypes'));
    }

    public function edit(Job $job): View
    {
        $sites       = Site::with('client')->orderBy('name')->get();
        $technicians = User::role('field-technician')->orderBy('name')->get();
        $buildings   = Building::where('site_id', $job->site_id)->orderBy('name_or_level')->get();

        return view('admin.jobs.edit', compact('job', 'sites', 'technicians', 'buildings'));
    }

    public function update(Request $request, Job $job): RedirectResponse
    {
        $data = $request->validate([
            'work_type'      => ['required', 'in:' . implode(',', array_keys(Job::WORK_TYPES))],
            'status'         => ['required', 'in:' . implode(',', array_keys(Job::STATUSES))],
            'scheduled_date' => ['nullable', 'date'],
            'scope_notes'    => ['nullable', 'string'],
            'technician_ids' => ['nullable', 'array'],
            'technician_ids.*' => ['exists:users,id'],
            'building_ids'   => ['nullable', 'array'],
            'building_ids.*' => ['exists:buildings,id'],
        ]);

        // Validate the status transition
        $nextStatuses = $job->nextStatuses();
        if ($data['status'] !== $job->status && !in_array($data['status'], $nextStatuses)) {
            return back()->withInput()->withErrors([
                'status' => "Cannot transition from '{$job->status}' to '{$data['status']}'.",
            ]);
        }

        $job->update([
            'work_type'      => $data['work_type'],
            'status'         => $data['status'],
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'scope_notes'    => $data['scope_notes'] ?? null,
        ]);

        $job->technicians()->sync($data['technician_ids'] ?? []);
        $job->buildings()->sync($data['building_ids'] ?? []);

        return redirect()->route('admin.jobs.show', $job)
            ->with('success', 'Job updated successfully.');
    }
}
