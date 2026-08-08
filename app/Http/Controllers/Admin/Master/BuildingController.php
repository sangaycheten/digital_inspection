<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuildingController extends Controller
{
    public function index(Request $request): View
    {
        $buildings = Building::with('site.client')
            ->when($request->search, fn ($q) => $q->where('name_or_level', 'like', "%{$request->search}%"))
            ->when($request->site_id, fn ($q) => $q->where('site_id', $request->site_id))
            ->latest()->paginate(15)->withQueryString();

        $sites = Site::with('client')->orderBy('address')->get();

        return view('admin.master.buildings.index', compact('buildings', 'sites'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_id'       => ['required', 'exists:sites,id'],
            'name_or_level' => ['required', 'string', 'max:255'],
            'roof_zones'    => ['nullable', 'string'],
        ]);

        $data['roof_zones'] = $data['roof_zones']
            ? array_filter(array_map('trim', explode(',', $data['roof_zones'])))
            : null;

        $building = Building::create($data);

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($building)->event('created')
            ->log("Building created: {$building->name_or_level}");

        return redirect()->route('admin.master.buildings.index')
            ->with('success', "Building \"{$building->name_or_level}\" created successfully.");
    }

    public function update(Request $request, Building $building): RedirectResponse
    {
        $data = $request->validate([
            'site_id'       => ['required', 'exists:sites,id'],
            'name_or_level' => ['required', 'string', 'max:255'],
            'roof_zones'    => ['nullable', 'string'],
        ]);

        $data['roof_zones'] = $data['roof_zones']
            ? array_filter(array_map('trim', explode(',', $data['roof_zones'])))
            : null;

        $building->update($data);

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($building)->event('updated')
            ->log("Building updated: {$building->name_or_level}");

        return redirect()->route('admin.master.buildings.index')
            ->with('success', "Building \"{$building->name_or_level}\" updated successfully.");
    }

    public function destroy(Building $building): RedirectResponse
    {
        $name = $building->name_or_level;
        $building->delete();

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($building)->event('deleted')
            ->log("Building deleted: {$name}");

        return redirect()->route('admin.master.buildings.index')
            ->with('success', "Building \"{$name}\" deleted.");
    }
}
