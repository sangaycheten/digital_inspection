<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user    = Auth::user();
        $siteIds = $user->sites->pluck('id');

        $jobs = Job::whereIn('site_id', $siteIds)
            ->where('client_id', $user->client_id)
            ->whereIn('status', ['issued', 'closed'])
            ->where('certificate_accessible', true)
            ->with(['site'])
            ->withCount('inspectionRecords')
            ->when($request->site_id,   fn ($q) => $q->where('site_id', $request->site_id))
            ->when($request->work_type, fn ($q) => $q->where('work_type', $request->work_type))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $sites     = $user->sites;
        $workTypes = \App\Models\Job::WORK_TYPES;

        return view('client.reports.index', compact('jobs', 'sites', 'workTypes'));
    }
}
