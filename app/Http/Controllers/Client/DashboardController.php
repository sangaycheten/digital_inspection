<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Job;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user    = Auth::user();
        $siteIds = $user->sites->pluck('id');

        $sitesCount  = $siteIds->count();
        $assetsCount = Asset::whereIn('site_id', $siteIds)->count();

        $assetsByStatus = Asset::whereIn('site_id', $siteIds)
            ->selectRaw('current_status, count(*) as total')
            ->groupBy('current_status')
            ->pluck('total', 'current_status');

        $issuedJobsCount = Job::whereIn('site_id', $siteIds)
            ->where('client_id', $user->client_id)
            ->whereIn('status', ['issued', 'closed'])
            ->count();

        $dueAssets = Asset::whereIn('site_id', $siteIds)
            ->whereNotNull('next_inspection_due_date')
            ->whereDate('next_inspection_due_date', '<=', now()->addDays(90))
            ->orderBy('next_inspection_due_date')
            ->with('site')
            ->limit(5)
            ->get();

        $recentJobs = Job::whereIn('site_id', $siteIds)
            ->where('client_id', $user->client_id)
            ->whereIn('status', ['issued', 'closed', 'approved', 'submitted_for_review', 'under_review'])
            ->with('site')
            ->latest()
            ->limit(5)
            ->get();

        $upcomingJobs = Job::whereIn('site_id', $siteIds)
            ->where('client_id', $user->client_id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->with('site')
            ->orderBy('scheduled_date')
            ->limit(5)
            ->get();

        $totalAssets = $assetsByStatus->sum();
        $passCount   = $assetsByStatus['pass'] ?? 0;
        $failCount   = $assetsByStatus['fail'] ?? 0;
        $otherCount  = $totalAssets - $passCount - $failCount;
        $compliance  = $totalAssets > 0 ? round(($passCount / $totalAssets) * 100) : null;

        return view('client.dashboard', compact(
            'sitesCount', 'assetsCount', 'assetsByStatus',
            'issuedJobsCount', 'dueAssets', 'recentJobs', 'upcomingJobs',
            'totalAssets', 'passCount', 'failCount', 'otherCount', 'compliance'
        ));
    }
}
