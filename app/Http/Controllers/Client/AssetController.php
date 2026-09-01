<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\MasterLookup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function show(Asset $asset): View
    {
        /** @var \App\Models\User $user */
        $user    = Auth::user();
        $siteIds = $user->sites->pluck('id');

        abort_if(!$siteIds->contains($asset->site_id), 403);

        $asset->load([
            'site', 'building',
            'inspectionRecords' => fn ($q) => $q->with(['job', 'technician'])->orderByDesc('inspection_date'),
        ]);

        $assetTypes = MasterLookup::assetTypeMap();

        return view('client.assets.show', compact('asset', 'assetTypes'));
    }

    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user    = Auth::user();
        $siteIds = $user->sites->pluck('id');

        $assets = Asset::whereIn('site_id', $siteIds)
            ->with(['site', 'building'])
            ->when($request->site_id,    fn ($q) => $q->where('site_id', $request->site_id))
            ->when($request->asset_type, fn ($q) => $q->where('asset_type', $request->asset_type))
            ->when($request->status,     fn ($q) => $q->where('current_status', $request->status))
            ->when($request->search,     fn ($q) => $q->where('asset_code', 'like', "%{$request->search}%"))
            ->orderBy('asset_type')
            ->orderBy('asset_code')
            ->paginate(25)
            ->withQueryString();

        $sites      = $user->sites;
        $assetTypes = MasterLookup::assetTypeMap();

        $statusOptions = [
            'pass'           => 'Pass',
            'fail'           => 'Fail',
            'restricted_use' => 'Restricted Use',
            'not_inspected'  => 'Not Inspected',
            'not_located'    => 'Not Located',
            'removed'        => 'Removed',
        ];

        return view('client.assets.index', compact('assets', 'sites', 'assetTypes', 'statusOptions'));
    }
}
