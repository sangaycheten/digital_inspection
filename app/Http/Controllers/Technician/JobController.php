<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\InspectionRecord;
use App\Models\Job;
use App\Models\MasterLookup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $userId = Auth::id();

        $jobs = Job::with(['site', 'client'])
            ->whereHas('technicians', fn ($q) => $q->where('users.id', $userId))
            ->when($request->work_type, fn ($q) => $q->where('work_type', $request->work_type))
            ->when($request->status,    fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Load assigned_at per job from the pivot
        $jobIds = $jobs->pluck('id');
        $assignedAtMap = \Illuminate\Support\Facades\DB::table('job_technician_buildings')
            ->whereIn('job_id', $jobIds)
            ->where('user_id', $userId)
            ->selectRaw('job_id, MIN(created_at) as assigned_at')
            ->groupBy('job_id')
            ->pluck('assigned_at', 'job_id');

        return view('technician.jobs.index', compact('jobs', 'assignedAtMap'));
    }

    public function show(Job $job): View
    {
        abort_if(!$job->technicians()->where('users.id', Auth::id())->exists(), 403);

        $job->load([
            'site',
            'client',
            'buildings',
            'technicians',
            'targetAssets.asset',
            'installationAssets.asset',
            'inspectionRecords.asset',
        ]);

        $buildingIds = $job->assignedBuildingIdsForTechnician(Auth::id());

        $siteAssets = Asset::with('building')
            ->where('site_id', $job->site_id)
            ->when($buildingIds->isNotEmpty(), fn ($q) => $q->whereIn('building_id', $buildingIds))
            ->whereNotIn('current_status', ['removed', 'replaced'])
            ->orderBy('asset_type')
            ->orderBy('asset_code')
            ->get();

        $totalAssets = $siteAssets->count();

        $inspectionSummary = InspectionRecord::where('job_id', $job->id)
            ->selectRaw('document_status, count(distinct asset_id) as cnt')
            ->groupBy('document_status')
            ->pluck('cnt', 'document_status');

        $inspectedCount = $inspectionSummary->sum();
        $assetTypes     = MasterLookup::assetTypeMap();

        return view('technician.jobs.show', compact('job', 'inspectedCount', 'totalAssets', 'inspectionSummary', 'assetTypes', 'siteAssets'));
    }

    public function submitForReview(Job $job): RedirectResponse
    {
        abort_if(!$job->technicians()->where('users.id', Auth::id())->exists(), 403);
        abort_if($job->isClosed(), 403, 'This job is closed.');

        $drafts = InspectionRecord::where('job_id', $job->id)
            ->where('document_status', 'draft')
            ->get();

        // Also allow re-syncing job status when records are already submitted but job wasn't advanced
        $hasSubmitted = InspectionRecord::where('job_id', $job->id)
            ->where('document_status', 'submitted')
            ->exists();

        if ($drafts->isEmpty() && !$hasSubmitted) {
            return back()->withErrors(['submit' => 'No inspection records to submit.']);
        }

        // Submit drafts inside transaction (record-level atomicity only)
        if ($drafts->isNotEmpty()) {
            DB::transaction(function () use ($drafts) {
                foreach ($drafts as $record) {
                    $record->update([
                        'document_status' => 'submitted',
                        'required_action' => $record->required_action
                            ? ltrim(str_replace('[REJECTED]', '', $record->required_action))
                            : null,
                    ]);
                }
            });
        }

        // Update job status separately (after records are committed) to avoid stale-model issues
        if (in_array($job->status, ['scheduled', 'in_progress', 'rectification_required'])) {
            $job->update(['status' => 'submitted_for_review']);
        }

        $count = $drafts->count() ?: InspectionRecord::where('job_id', $job->id)->where('document_status', 'submitted')->count();

        return redirect()->route('technician.jobs.show', $job)
            ->with('success', $count . ' inspection record(s) submitted for review.');
    }

    public function storeAsset(Request $request, Job $job): RedirectResponse
    {
        abort_if(!$job->technicians()->where('users.id', Auth::id())->exists(), 403);
        abort_if($job->isClosed(), 403, 'This job is closed.');

        $data = $request->validate([
            'asset_code'              => ['required', 'string', 'max:255'],
            'asset_type'              => ['required', Rule::exists('master_lookups', 'value')->where('category', 'asset_type')],
            'building_id'             => [$job->buildings()->exists() ? 'required' : 'nullable', 'exists:buildings,id'],
            'zone'                    => ['nullable', 'string', 'max:100'],
            'group_id'                => ['nullable', 'string', 'max:100'],
            'make'                    => ['nullable', 'string', 'max:255'],
            'model'                   => ['nullable', 'string', 'max:255'],
            'serial_or_batch'         => ['nullable', 'string', 'max:100'],
            'rating'                  => ['nullable', 'string', 'max:100'],
            'fixing_type'             => ['nullable', 'string', 'max:100'],
            'install_date'            => ['nullable', 'date'],
            'next_inspection_due_date' => ['nullable', 'date'],
        ]);

        $exists = Asset::where('site_id', $job->site_id)
            ->where('asset_code', $data['asset_code'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'asset_code' => "Asset code '{$data['asset_code']}' already exists at this site.",
            ]);
        }

        Asset::create([
            ...$data,
            'site_id'    => $job->site_id,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('technician.jobs.show', $job)
            ->with('success', "Asset {$data['asset_code']} added to the register.");
    }
}
