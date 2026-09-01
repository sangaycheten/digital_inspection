<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Job;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $sites = $user->sites()->with(['buildings', 'client'])->get();

        $siteIds     = $sites->pluck('id');
        $assetCounts = Asset::whereIn('site_id', $siteIds)
            ->selectRaw('site_id, count(*) as total')
            ->groupBy('site_id')
            ->pluck('total', 'site_id');

        $lastJobDates = Job::whereIn('site_id', $siteIds)
            ->whereIn('status', ['issued', 'closed'])
            ->selectRaw('site_id, max(updated_at) as last_date')
            ->groupBy('site_id')
            ->pluck('last_date', 'site_id');

        return view('client.sites.index', compact('sites', 'assetCounts', 'lastJobDates'));
    }
}
