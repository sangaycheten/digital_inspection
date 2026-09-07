<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\InspectionRecord;
use App\Models\Job;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();

        $activeStatuses    = ['new', 'scheduled', 'in_progress', 'rectification_required'];
        $completedStatuses = ['submitted_for_review', 'under_review', 'approved', 'issued', 'closed'];

        $myJobs = fn () => Job::whereHas('technicians', fn ($q) => $q->where('users.id', $userId));

        // ── KPI cards ────────────────────────────────────────────────────────
        $activeJobsCount    = $myJobs()->whereIn('status', $activeStatuses)->count();
        $pendingReviewCount = $myJobs()->where('status', 'submitted_for_review')->count();
        $completedCount     = $myJobs()->whereIn('status', ['approved', 'issued', 'closed'])->count();
        $inspectionCount    = InspectionRecord::where('technician_id', $userId)->count();

        // ── Work type counts for quick-capture buttons ────────────────────────
        $workTypeCounts = $myJobs()
            ->whereIn('status', $activeStatuses)
            ->selectRaw('work_type, count(*) as total')
            ->groupBy('work_type')
            ->pluck('total', 'work_type');

        // ── Job status breakdown (my jobs only) ───────────────────────────────
        $jobsByStatus = $myJobs()
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        // ── Today's scheduled jobs ────────────────────────────────────────────
        $todayJobs = $myJobs()
            ->whereDate('scheduled_date', today())
            ->with(['site', 'client'])
            ->orderBy('scheduled_date')
            ->get();

        // ── All active jobs ───────────────────────────────────────────────────
        $activeJobs = $myJobs()
            ->whereIn('status', $activeStatuses)
            ->with(['site', 'client'])
            ->orderByRaw("FIELD(status, 'in_progress', 'rectification_required', 'scheduled', 'new')")
            ->get();

        // ── Recent completed jobs ─────────────────────────────────────────────
        $recentCompleted = $myJobs()
            ->whereIn('status', $completedStatuses)
            ->with(['site', 'client'])
            ->latest()
            ->take(5)
            ->get();

        return view('technician.dashboard', compact(
            'activeJobsCount', 'pendingReviewCount', 'completedCount', 'inspectionCount',
            'workTypeCounts',
            'jobsByStatus',
            'todayJobs',
            'activeJobs',
            'recentCompleted',
        ));
    }
}
