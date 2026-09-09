<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Building;
use App\Models\InspectionRecord;
use App\Models\MasterLookup;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $isHistory = (bool) $request->history;

        $applyFilters = function ($q) use ($request, $isHistory) {
            return $q
                ->when($request->search, fn ($q) =>
                    $q->where('asset_code', 'like', "%{$request->search}%")
                      ->orWhere('make', 'like', "%{$request->search}%")
                      ->orWhere('model', 'like', "%{$request->search}%")
                      ->orWhere('serial_or_batch', 'like', "%{$request->search}%")
                )
                ->when($request->site_id,     fn ($q) => $q->where('site_id', $request->site_id))
                ->when($request->building_id, fn ($q) => $q->where('building_id', $request->building_id))
                ->when($request->status,
                    fn ($q) => $q->where('current_status', $request->status),
                    fn ($q) => $isHistory
                        ? $q->whereIn('current_status', ['removed', 'replaced'])   // History: only retired assets
                        : $q->whereNotIn('current_status', ['removed', 'replaced']) // Manage: hide retired assets
                );
        };

        // Count per type (respects all filters except asset_type — that's the tab)
        $typeCounts = $applyFilters(Asset::query())
            ->selectRaw('asset_type, count(*) as cnt')
            ->groupBy('asset_type')
            ->orderBy('asset_type')
            ->pluck('cnt', 'asset_type');

        // Default to first type with assets if none selected
        $activeType = $request->asset_type && $typeCounts->has($request->asset_type)
            ? $request->asset_type
            : $typeCounts->keys()->first();

        $assets = $applyFilters(Asset::with(['site.client', 'building']))
            ->when($activeType, fn ($q) => $q->where('asset_type', $activeType))
            ->orderBy('asset_code')
            ->paginate(20)
            ->withQueryString();

        $sites      = Site::orderBy('name')->get();
        $buildings  = $request->site_id
            ? Building::where('site_id', $request->site_id)->orderBy('name_or_level')->get()
            : collect();
        $assetTypes = MasterLookup::assetTypeMap();

        return view('admin.assets.index', compact('assets', 'sites', 'buildings', 'assetTypes', 'typeCounts', 'activeType'));
    }

    public function create(): View
    {
        $sites      = Site::with('client')->orderBy('name')->get();
        $assets     = Asset::orderBy('asset_code')->get(['id', 'asset_code', 'site_id']);
        $assetTypes = MasterLookup::assetTypeMap();

        return view('admin.assets.create', compact('sites', 'assets', 'assetTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->input('mode') === 'range') {
            return $this->storeRange($request);
        }

        $data = $request->validate([
            'site_id'                  => ['required', 'exists:sites,id'],
            'building_id'              => ['nullable', 'exists:buildings,id'],
            'zone'                     => ['nullable', 'string', 'max:255'],
            'asset_code'               => ['required', 'string', 'max:255'],
            'asset_type'               => ['required', Rule::exists('master_lookups', 'value')->where('category', 'asset_type')],
            'make'                     => ['nullable', 'string', 'max:255'],
            'model'                    => ['nullable', 'string', 'max:255'],
            'serial_or_batch'          => ['nullable', 'string', 'max:255'],
            'rating'                   => ['nullable', 'string', 'max:255'],
            'fixing_type'              => ['nullable', 'string', 'max:255'],
            'install_date'             => ['nullable', 'date'],
            'next_inspection_due_date' => ['nullable', 'date'],
            'replaces_asset_id'        => ['nullable', 'exists:assets,id'],
        ], [
            'asset_code.required' => 'Asset code is required.',
            'asset_type.in'       => 'Invalid asset type selected.',
        ]);

        $site         = Site::with('client')->find($data['site_id']);
        $buildingCode = $data['building_id'] ? Building::find($data['building_id'])?->building_code : null;
        $locParts     = array_filter([$site?->client?->custom_client_code, $buildingCode]);
        $prefix       = ($locParts ? implode('-', $locParts) . '-' : '') . $data['asset_type'];
        $data['asset_code'] = $prefix . $data['asset_code'];

        $exists = Asset::where('site_id', $data['site_id'])
            ->where('asset_code', $data['asset_code'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'asset_code' => "Asset code '{$data['asset_code']}' already exists for this site.",
            ]);
        }

        $data['created_by'] = Auth::id();
        $asset = Asset::create($data);

        if ($asset->replaces_asset_id) {
            $old = Asset::find($asset->replaces_asset_id);
            $old?->update([
                'replaced_by_asset_id' => $asset->id,
                'current_status'       => 'replaced',
            ]);
        }

        return redirect()->route('admin.assets.show', $asset)
            ->with('success', "Asset {$asset->asset_code} created successfully.");
    }

    private function storeRange(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_id'                  => ['required', 'exists:sites,id'],
            'building_id'              => ['nullable', 'exists:buildings,id'],
            'zone'                     => ['nullable', 'string', 'max:255'],
            'range_start'              => ['required', 'regex:/^\d+$/'],
            'range_end'                => ['required', 'regex:/^\d+$/', 'gte:range_start'],
            'quantity'                 => ['required', 'integer', 'min:1'],
            'asset_type'               => ['required', Rule::exists('master_lookups', 'value')->where('category', 'asset_type')],
            'make'                     => ['nullable', 'string', 'max:255'],
            'model'                    => ['nullable', 'string', 'max:255'],
            'rating'                   => ['nullable', 'string', 'max:255'],
            'fixing_type'              => ['nullable', 'string', 'max:255'],
            'install_date'             => ['nullable', 'date'],
            'next_inspection_due_date' => ['nullable', 'date'],
        ]);

        $start       = (int) $data['range_start'];
        $end         = (int) $data['range_end'];
        $rangeLength = $end - $start + 1;

        if ((int) $data['quantity'] !== $rangeLength) {
            return back()->withInput()->withErrors([
                'quantity' => "Quantity must equal end − start + 1 = {$rangeLength}.",
            ]);
        }

        $padLength    = strlen($request->input('range_end'));
        $site         = Site::with('client')->find($data['site_id']);
        $buildingCode = $data['building_id'] ? Building::find($data['building_id'])?->building_code : null;
        $locParts     = array_filter([$site?->client?->custom_client_code, $buildingCode]);
        $prefix       = ($locParts ? implode('-', $locParts) . '-' : '') . $data['asset_type'];
        $pad          = fn (int $n) => str_pad($n, $padLength, '0', STR_PAD_LEFT);

        // Verify every code is unique before inserting any
        for ($i = $start; $i <= $end; $i++) {
            $code = $prefix . $pad($i);
            if (Asset::where('site_id', $data['site_id'])->where('asset_code', $code)->exists()) {
                return back()->withInput()->withErrors([
                    'range_start' => "Asset code '{$code}' already exists for this site.",
                ]);
            }
        }

        $groupId = (string) Str::uuid();

        for ($i = $start; $i <= $end; $i++) {
            Asset::create([
                'site_id'                  => $data['site_id'],
                'building_id'              => $data['building_id'] ?? null,
                'zone'                     => $data['zone'] ?? null,
                'asset_code'               => $prefix . $pad($i),
                'asset_type'               => $data['asset_type'],
                'group_id'                 => $groupId,
                'make'                     => $data['make'] ?? null,
                'model'                    => $data['model'] ?? null,
                'rating'                   => $data['rating'] ?? null,
                'fixing_type'              => $data['fixing_type'] ?? null,
                'install_date'             => $data['install_date'] ?? null,
                'next_inspection_due_date' => $data['next_inspection_due_date'] ?? null,
                'created_by'               => Auth::id(),
            ]);
        }

        $first = $prefix . $pad($start);
        $last  = $prefix . $pad($end);

        return redirect()->route('admin.assets.index')
            ->with('success', "{$rangeLength} assets ({$first}–{$last}) created successfully.");
    }

    public function show(Asset $asset): View
    {
        $asset->load([
            'site.client',
            'building',
            'creator',
            'editor',
            'currentInspection.technician',
            'inspectionRecords.technician',
            'replacesAsset',
            'replacedByAsset.creator',
        ]);

        $assetTypes = MasterLookup::assetTypeMap();

        return view('admin.assets.show', compact('asset', 'assetTypes'));
    }

    public function edit(Asset $asset): View
    {
        $sites       = Site::with('client')->orderBy('name')->get();
        $buildings   = Building::where('site_id', $asset->site_id)->orderBy('name_or_level')->get();
        $otherAssets = Asset::where('site_id', $asset->site_id)
            ->where('id', '!=', $asset->id)
            ->orderBy('asset_code')
            ->get(['id', 'asset_code']);
        $assetTypes  = MasterLookup::assetTypeMap();

        return view('admin.assets.edit', compact('asset', 'sites', 'buildings', 'otherAssets', 'assetTypes'));
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $data = $request->validate([
            'site_id'                 => ['required', 'exists:sites,id'],
            'building_id'             => ['nullable', 'exists:buildings,id'],
            'zone'                    => ['nullable', 'string', 'max:255'],
            'asset_code'              => ['required', 'string', 'max:255'],
            'asset_type'              => ['required', Rule::exists('master_lookups', 'value')->where('category', 'asset_type')],
            'make'                    => ['nullable', 'string', 'max:255'],
            'model'                   => ['nullable', 'string', 'max:255'],
            'serial_or_batch'         => ['nullable', 'string', 'max:255'],
            'rating'                  => ['nullable', 'string', 'max:255'],
            'fixing_type'             => ['nullable', 'string', 'max:255'],
            'install_date'            => ['nullable', 'date'],
            'next_inspection_due_date'=> ['nullable', 'date'],
        ]);

        // Unique asset_code per site (exclude self)
        $exists = Asset::where('site_id', $data['site_id'])
            ->where('asset_code', $data['asset_code'])
            ->where('id', '!=', $asset->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'asset_code' => "Asset code '{$data['asset_code']}' already exists for this site.",
            ]);
        }

        $asset->update($data);

        return redirect()->route('admin.assets.show', $asset)
            ->with('success', "Asset {$asset->asset_code} updated successfully.");
    }

    public function remove(Asset $asset): RedirectResponse
    {
        $asset->update(['current_status' => 'removed']);

        activity()->useLog('asset')
            ->causedBy(request()->user())
            ->performedOn($asset)
            ->event('removed')
            ->log("Asset {$asset->asset_code} marked as removed.");

        return redirect()->route('admin.assets.show', $asset)
            ->with('success', "Asset {$asset->asset_code} has been marked as removed.");
    }

    public function reinstate(Asset $asset): RedirectResponse
    {
        $asset->update(['current_status' => 'not_inspected']);

        activity()->useLog('asset')
            ->causedBy(request()->user())
            ->performedOn($asset)
            ->event('reinstated')
            ->log("Asset {$asset->asset_code} reinstated (status reset to not inspected).");

        return redirect()->route('admin.assets.show', $asset)
            ->with('success', "Asset {$asset->asset_code} has been reinstated.");
    }

    public function notLocated(Asset $asset): RedirectResponse
    {
        $asset->update(['current_status' => 'not_located']);

        activity()->useLog('asset')
            ->causedBy(request()->user())
            ->performedOn($asset)
            ->event('not_located')
            ->log("Asset {$asset->asset_code} marked as not located.");

        return redirect()->route('admin.assets.show', $asset)
            ->with('success', "Asset {$asset->asset_code} has been marked as not located.");
    }

    public function history(Request $request): View
    {
        $records = InspectionRecord::with(['asset.site.client', 'asset.building', 'technician', 'job'])
            ->when($request->search, fn ($q) =>
                $q->whereHas('asset', fn ($a) =>
                    $a->where('asset_code', 'like', "%{$request->search}%")
                )
            )
            ->when($request->site_id, fn ($q) =>
                $q->whereHas('asset', fn ($a) => $a->where('site_id', $request->site_id))
            )
            ->when($request->building_id, fn ($q) =>
                $q->whereHas('asset', fn ($a) => $a->where('building_id', $request->building_id))
            )
            ->when($request->asset_type, fn ($q) =>
                $q->whereHas('asset', fn ($a) => $a->where('asset_type', $request->asset_type))
            )
            ->when($request->result, fn ($q) => $q->where('result', $request->result))
            ->when($request->date_from, fn ($q) => $q->where('inspection_date', '>=', $request->date_from))
            ->when($request->date_to,   fn ($q) => $q->where('inspection_date', '<=', $request->date_to))
            ->orderByDesc('inspection_date')
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $sites      = Site::orderBy('name')->get();
        $buildings  = $request->site_id
            ? Building::where('site_id', $request->site_id)->orderBy('name_or_level')->get()
            : collect();
        $assetTypes = MasterLookup::assetTypeMap();

        return view('admin.assets.history', compact('records', 'sites', 'buildings', 'assetTypes'));
    }
}
