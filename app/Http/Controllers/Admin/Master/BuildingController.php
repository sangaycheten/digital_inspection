<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Client;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BuildingController extends Controller
{
    public function index(Request $request): View
    {
        $buildings = Building::with('site.client')
            ->when($request->search, fn ($q) => $q->where('name_or_level', 'like', "%{$request->search}%"))
            ->when($request->site_id, fn ($q) => $q->where('site_id', $request->site_id))
            ->latest()->paginate(15)->withQueryString();

        $sites   = Site::with('client')->orderBy('address')->get();
        $clients = Client::orderBy('name')->get();

        return view('admin.master.buildings.index', compact('buildings', 'sites', 'clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_id'       => ['required', 'exists:sites,id'],
            'name_or_level' => [
                'required', 'string', 'max:255',
                Rule::unique('buildings', 'name_or_level')->where('site_id', $request->site_id),
            ],
            'roof_zones'    => ['nullable', 'string'],
        ], [
            'name_or_level.unique' => 'A building with this name already exists at the selected site.',
        ]);

        $data['roof_zones'] = $this->parseRoofZones($data['roof_zones'] ?? null);

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
            'name_or_level' => [
                'required', 'string', 'max:255',
                Rule::unique('buildings', 'name_or_level')->where('site_id', $request->site_id)->ignore($building->id),
            ],
            'roof_zones'    => ['nullable', 'string'],
        ], [
            'name_or_level.unique' => 'A building with this name already exists at the selected site.',
        ]);

        $data['roof_zones'] = $this->parseRoofZones($data['roof_zones'] ?? null);

        $building->update($data);

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($building)->event('updated')
            ->log("Building updated: {$building->name_or_level}");

        return redirect()->route('admin.master.buildings.index')
            ->with('success', "Building \"{$building->name_or_level}\" updated successfully.");
    }

    private function parseRoofZones(?string $raw): ?array
    {
        if (!$raw || trim($raw) === '' || trim($raw) === '[]') return null;
        $decoded = json_decode($raw, true);
        return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? ($decoded ?: null) : null;
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
