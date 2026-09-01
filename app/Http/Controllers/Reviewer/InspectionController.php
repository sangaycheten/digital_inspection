<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Client;
use App\Models\InspectionRecord;
use App\Models\Job;
use App\Models\MasterLookup;
use App\Models\Questionnaire;
use App\Notifications\InspectionRejectedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class InspectionController extends Controller
{
    private function scopeToManager(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        $clientIds = Client::where('manager_id', Auth::id())->pluck('id');

        if ($clientIds->isEmpty()) {
            return $query->whereRaw('1 = 0'); // no clients assigned → see nothing
        }

        return $query->whereHas('asset.site', fn ($q) => $q->whereIn('client_id', $clientIds));
    }

    public function index(Request $request): View
    {
        $clientIds = Client::where('manager_id', Auth::id())->pluck('id');

        $pendingCount  = $this->scopeToManager(InspectionRecord::query())
            ->where('document_status', 'submitted')->count();

        $approvedTodayIds = Activity::where('log_name', 'inspection_record')
            ->where('description', 'updated')
            ->whereJsonContains('properties->attributes->document_status', 'approved')
            ->whereDate('created_at', today())
            ->pluck('subject_id');
        $approvedToday = $this->scopeToManager(InspectionRecord::query())
            ->whereIn('id', $approvedTodayIds)
            ->count();

        $technicians = \App\Models\User::role('field-technician')->orderBy('name')->get();

        // Drill-down: wizard review for one job
        if ($request->filled('job_id')) {
            $job = Job::with(['client', 'site', 'technicians', 'buildings'])->findOrFail($request->job_id);

            abort_if(
                $clientIds->isNotEmpty() && !$clientIds->contains($job->client_id),
                403, 'You are not assigned to this client.'
            );

            // Deduplicate: keep only the latest record per asset to avoid double-counting
            // when a record was rejected (draft) and a prior approved one still exists.
            $allRecords = InspectionRecord::with(['asset.building', 'technician', 'answers', 'previousInspection'])
                ->where('job_id', $job->id)
                ->orderBy('created_at')
                ->get()
                ->groupBy('asset_id')
                ->map(fn ($recs) => $recs->sortByDesc('created_at')->first())
                ->values();

            $recordsByType = $allRecords->groupBy(fn ($r) => $r->asset->asset_type ?? 'unknown');

            $usedTypes = $allRecords->pluck('asset.asset_type')->unique()->filter();
            $questionsByType = Questionnaire::whereIn('asset_type', $usedTypes)
                ->whereNull('parent_id')
                ->where('status', 'active')
                ->where('enabled', true)
                ->with(['subQuestionnaires' => fn ($q) => $q->where('status', 'active')->where('enabled', true)->orderBy('created_at')])
                ->orderBy('created_at')
                ->get()
                ->groupBy('asset_type');

            $assetTypes = MasterLookup::assetTypeMap();

            return view('reviewer.inspections.index', compact(
                'allRecords', 'recordsByType', 'job', 'technicians',
                'pendingCount', 'approvedToday', 'questionsByType', 'assetTypes'
            ));
        }

        // Default: job-level grouped view — hide fully approved/closed jobs unless explicitly requested
        $jobs = \App\Models\Job::whereHas('inspectionRecords')
            ->when($clientIds->isNotEmpty(), fn ($q) => $q->whereIn('client_id', $clientIds))
            ->when($clientIds->isEmpty(),    fn ($q) => $q->whereRaw('1 = 0'))
            ->with(['client', 'site', 'technicians'])
            ->withCount([
                'inspectionRecords',
                'inspectionRecords as submitted_count' => fn ($q) => $q->where('document_status', 'submitted'),
                'inspectionRecords as approved_count'  => fn ($q) => $q->where('document_status', 'approved'),
                'inspectionRecords as draft_count'     => fn ($q) => $q->where('document_status', 'draft'),
            ])
            ->when(!$request->filled('status'), fn ($q) => $q->whereIn('status', ['submitted_for_review', 'under_review', 'rectification_required']))
            ->when($request->status === 'submitted', fn ($q) => $q->whereHas('inspectionRecords', fn ($ir) => $ir->where('document_status', 'submitted')))
            ->when($request->status === 'approved',  fn ($q) => $q->whereIn('status', ['approved', 'issued', 'closed']))
            ->when($request->technician_id, fn ($q) => $q->whereHas('technicians', fn ($t) => $t->where('users.id', $request->technician_id)))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('reviewer.inspections.index', compact(
            'jobs', 'technicians', 'pendingCount', 'approvedToday'
        ));
    }

    public function show(InspectionRecord $inspection): View
    {
        $inspection->load([
            'asset.site', 'asset.building', 'technician',
            'previousInspection.technician',
            'answers.questionnaire',
        ]);
        $assetTypes  = MasterLookup::assetTypeMap();
        $activityLog = Activity::where('subject_type', InspectionRecord::class)
            ->where('subject_id', $inspection->id)
            ->with('causer')
            ->orderBy('created_at')
            ->get();

        return view('reviewer.inspections.show', compact('inspection', 'assetTypes', 'activityLog'));
    }

    public function approve(InspectionRecord $inspection): RedirectResponse
    {
        abort_if($inspection->document_status === 'approved', 422, 'Already approved.');

        DB::transaction(function () use ($inspection) {
            InspectionRecord::where('asset_id', $inspection->asset_id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            $inspection->update([
                'document_status' => 'approved',
                'is_current'      => true,
            ]);

            Asset::where('id', $inspection->asset_id)->update([
                'current_status'        => $inspection->result,
                'current_inspection_id' => $inspection->id,
            ]);
        });

        // Auto-advance job status
        $this->advanceJobAfterReview($inspection);

        return back()->with('success', "Inspection for asset {$inspection->asset->asset_code} approved.");
    }

    public function reject(Request $request, InspectionRecord $inspection): RedirectResponse
    {
        abort_if($inspection->document_status === 'approved', 422, 'Cannot reject an approved record.');

        $request->validate([
            'rejection_note' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($inspection, $request) {
            $inspection->update([
                'document_status' => 'draft',
                'required_action' => '[REJECTED] ' . trim($request->rejection_note),
            ]);

            $job = \App\Models\Job::find($inspection->job_id);
            if ($job && in_array($job->status, ['submitted_for_review', 'under_review'])) {
                $job->update(['status' => 'rectification_required']);
            }
        });

        // Notify the technician who submitted the inspection
        $technician = $inspection->technician;
        if ($technician) {
            $inspection->loadMissing(['asset.site.client', 'job']);
            $technician->notify(new InspectionRejectedNotification(
                inspection: $inspection,
                rejectionNote: trim($request->rejection_note),
            ));
        }

        return back()->with('warning', "Inspection for {$inspection->asset->asset_code} sent back to technician for revision.");
    }

    private function advanceJobAfterReview(InspectionRecord $inspection): void
    {
        if (!$inspection->job_id) return;

        $job = \App\Models\Job::find($inspection->job_id);
        if (!$job) return;

        // First reviewer action moves job from submitted_for_review → under_review
        if ($job->status === 'submitted_for_review') {
            $job->update(['status' => 'under_review']);
            $job->refresh();
        }

        // When no draft or submitted records remain, all are approved → advance job
        // Also handles rectification_required: if manager approves all records after a rejection,
        // the job should advance even if it was never re-set to under_review.
        if (in_array($job->status, ['under_review', 'rectification_required'])) {
            $remaining = InspectionRecord::where('job_id', $job->id)
                ->whereIn('document_status', ['draft', 'submitted'])
                ->count();

            if ($remaining === 0) {
                $job->update(['status' => 'approved']);
            }
        }
    }
}
