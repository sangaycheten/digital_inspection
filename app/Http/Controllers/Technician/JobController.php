<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\InspectionRecord;
use App\Models\Job;
use App\Models\MasterLookup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $jobs = Job::with(['site', 'client'])
            ->whereHas('technicians', fn ($q) => $q->where('users.id', Auth::id()))
            ->when($request->work_type, fn ($q) => $q->where('work_type', $request->work_type))
            ->when($request->status,    fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('technician.jobs.index', compact('jobs'));
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

        $inspectedCount = InspectionRecord::where('job_id', $job->id)->distinct('asset_id')->count('asset_id');
        $assetTypes     = MasterLookup::assetTypeMap();

        return view('technician.jobs.show', compact('job', 'inspectedCount', 'assetTypes'));
    }
}
