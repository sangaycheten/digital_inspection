<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Client;
use App\Models\ClientFeedback;
use App\Models\InspectionRecord;
use App\Models\Job;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(): View
    {
        // ── KPI cards ────────────────────────────────────────────────────────
        $totalJobs    = Job::count();
        $totalAssets  = Asset::count();
        $totalClients = Client::count();
        $totalUsers   = User::count();

        // ── Job pipeline ─────────────────────────────────────────────────────
        $jobsByStatus = Job::selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $activeJobs  = $jobsByStatus->only(['new', 'scheduled', 'in_progress'])->sum();
        $pendingJobs = $jobsByStatus->get('submitted_for_review', 0)
                     + $jobsByStatus->get('under_review', 0);
        $closedJobs  = $jobsByStatus->only(['approved', 'issued', 'closed'])->sum();

        // ── Job trend (last 6 months) ─────────────────────────────────────────
        $jobTrend = Job::selectRaw("DATE_FORMAT(created_at, '%b %Y') as label, DATE_FORMAT(created_at, '%Y-%m') as sort_key, count(*) as c")
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('label', 'sort_key')
            ->orderBy('sort_key')
            ->get();

        // ── Asset status breakdown ────────────────────────────────────────────
        $assetsByStatus = Asset::selectRaw('current_status, count(*) as c')
            ->groupBy('current_status')
            ->pluck('c', 'current_status');

        // ── Inspection overview ───────────────────────────────────────────────
        $overdueAssets = Asset::whereNotNull('next_inspection_due_date')
            ->whereDate('next_inspection_due_date', '<', today())
            ->count();

        $dueSoonAssets = Asset::whereNotNull('next_inspection_due_date')
            ->whereDate('next_inspection_due_date', '>=', today())
            ->whereDate('next_inspection_due_date', '<=', today()->addDays(30))
            ->count();

        $pendingReview = InspectionRecord::whereIn('document_status', ['submitted', 'submitted_for_review', 'under_review'])
            ->count();

        // ── Recent jobs ───────────────────────────────────────────────────────
        $recentJobs = Job::with(['site', 'client', 'technicians'])
            ->latest()
            ->take(8)
            ->get();

        // ── Users by role ─────────────────────────────────────────────────────
        $usersByRole = User::select('roles.name as role', DB::raw('count(*) as c'))
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->groupBy('roles.name')
            ->pluck('c', 'role');

        // ── Client feedback ───────────────────────────────────────────────────
        $feedbackStats = ClientFeedback::selectRaw('round(avg(overall_rating),1) as avg, count(*) as total')
            ->first();

        // ── Recent activity ───────────────────────────────────────────────────
        $recentActivity = Activity::with('causer')
            ->latest()
            ->take(8)
            ->get();

        // ── Top clients by job count ──────────────────────────────────────────
        $topClients = Client::withCount('jobs')
            ->orderByDesc('jobs_count')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalJobs', 'totalAssets', 'totalClients', 'totalUsers',
            'jobsByStatus', 'activeJobs', 'pendingJobs', 'closedJobs',
            'jobTrend',
            'assetsByStatus',
            'overdueAssets', 'dueSoonAssets', 'pendingReview',
            'recentJobs',
            'usersByRole',
            'feedbackStats',
            'recentActivity',
            'topClients',
        ));
    }
}
