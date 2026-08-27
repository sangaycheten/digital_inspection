<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\InspectionRecord;
use App\Models\Job;
use App\Models\MasterLookup;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class InspectionController extends Controller
{
    public function index(Request $request): View
    {
        $totalJobs     = Job::whereHas('inspectionRecords')->count();
        $totalRecords  = InspectionRecord::count();
        $pendingCount  = InspectionRecord::where('document_status', 'submitted')->count();
        $pendingJobs   = Job::whereHas('inspectionRecords', fn ($q) => $q->where('document_status', 'submitted'))->count();
        $sentBackCount = InspectionRecord::where('document_status', 'draft')
                            ->where('required_action', 'like', '[REJECTED]%')->count();
        $sentBackJobs  = Job::where('status', 'rectification_required')
                            ->whereHas('inspectionRecords')->count();
        $approvedTotal = InspectionRecord::where('document_status', 'approved')->count();
        $approvedToday = InspectionRecord::where('document_status', 'approved')->whereDate('created_at', today())->count();

        $technicians = User::role('field-technician')->orderBy('name')->get();
        $managers    = User::role('manager')->orderBy('name')->get();
        $sites       = Site::orderBy('name')->get();
        $clients     = Client::orderBy('name')->get();

        // Drill-down: show individual records for one specific job
        if ($request->filled('job_id')) {
            $job = Job::with(['client.manager', 'site', 'technicians'])->findOrFail($request->job_id);

            $records = InspectionRecord::with(['asset.building', 'technician'])
                ->where('job_id', $job->id)
                ->when($request->status, fn ($q, $s) => $q->where('document_status', $s))
                ->when($request->result, fn ($q) => $q->where('result', $request->result))
                ->latest('inspection_date')
                ->paginate(50)
                ->withQueryString();

            return view('admin.inspections.index', compact(
                'records', 'job', 'technicians', 'managers', 'sites', 'clients',
                'totalJobs', 'totalRecords', 'pendingCount', 'pendingJobs',
                'sentBackCount', 'sentBackJobs', 'approvedTotal', 'approvedToday'
            ));
        }

        // Default: job-level grouped view
        $jobs = Job::whereHas('inspectionRecords')
            ->with(['client.manager', 'site', 'technicians'])
            ->withCount([
                'inspectionRecords',
                'inspectionRecords as draft_count'     => fn ($q) => $q->where('document_status', 'draft'),
                'inspectionRecords as submitted_count' => fn ($q) => $q->where('document_status', 'submitted'),
                'inspectionRecords as approved_count'  => fn ($q) => $q->where('document_status', 'approved'),
            ])
            ->when($request->client_id,     fn ($q) => $q->where('client_id', $request->client_id))
            ->when($request->site_id,       fn ($q) => $q->where('site_id', $request->site_id))
            ->when($request->technician_id, fn ($q) => $q->whereHas('technicians', fn ($t) => $t->where('users.id', $request->technician_id)))
            ->when($request->reviewer_id,   fn ($q) => $q->whereHas('client', fn ($c) => $c->where('manager_id', $request->reviewer_id)))
            ->when($request->status === 'submitted', fn ($q) => $q->whereHas('inspectionRecords', fn ($ir) => $ir->where('document_status', 'submitted')))
            ->when($request->status === 'approved',  fn ($q) => $q->whereHas('inspectionRecords', fn ($ir) => $ir->where('document_status', 'approved')))
            ->when($request->status === 'draft',     fn ($q) => $q->whereHas('inspectionRecords', fn ($ir) => $ir->where('document_status', 'draft')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.inspections.index', compact(
            'jobs', 'technicians', 'managers', 'sites', 'clients',
            'totalJobs', 'totalRecords', 'pendingCount', 'pendingJobs',
            'sentBackCount', 'sentBackJobs', 'approvedTotal', 'approvedToday'
        ));
    }

    public function show(InspectionRecord $inspection): View
    {
        $inspection->load([
            'asset.site.client',
            'asset.building',
            'technician',
            'previousInspection.technician',
            'answers.questionnaire',
        ]);

        $assetTypes  = MasterLookup::assetTypeMap();
        $activityLog = Activity::where('subject_type', InspectionRecord::class)
            ->where('subject_id', $inspection->id)
            ->with('causer')
            ->orderBy('created_at')
            ->get();

        return view('admin.inspections.show', compact('inspection', 'assetTypes', 'activityLog'));
    }
}
