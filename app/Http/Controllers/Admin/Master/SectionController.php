<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterLookup;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(Request $request): View
    {
        $sections = Section::when($request->search, fn ($q) =>
                $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->asset_type, fn ($q) => $q->where('asset_type', $request->asset_type))
            ->latest()->paginate(15)->withQueryString();

        $assetTypes = MasterLookup::assetTypeMap();

        return view('admin.master.sections.index', compact('sections', 'assetTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_type'  => ['required', 'string', Rule::exists('master_lookups', 'value')->where('category', 'asset_type')],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'in:active,inactive'],
        ]);

        $data['key'] = $this->makeUniqueKey($data['name']);

        $section = Section::create($data);

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($section)->event('created')
            ->withProperties(['attributes' => $data])
            ->log("Section created: {$section->name}");

        return redirect()->route('admin.master.sections.index')
            ->with('success', "Section \"{$section->name}\" created successfully.");
    }

    public function update(Request $request, Section $section): RedirectResponse
    {
        $data = $request->validate([
            'asset_type'  => ['required', 'string', Rule::exists('master_lookups', 'value')->where('category', 'asset_type')],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'in:active,inactive'],
        ]);

        $section->update($data);

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($section)->event('updated')
            ->log("Section updated: {$section->name}");

        return redirect()->route('admin.master.sections.index')
            ->with('success', "Section \"{$section->name}\" updated successfully.");
    }

    public function destroy(Section $section): RedirectResponse
    {
        $name = $section->name;
        $section->delete();

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($section)->event('deleted')
            ->log("Section deleted: {$name}");

        return redirect()->route('admin.master.sections.index')
            ->with('success', "Section \"{$name}\" deleted.");
    }

    private function makeUniqueKey(string $name): string
    {
        $base = preg_replace('/[^a-z0-9]+/', '_', strtolower(trim($name)));
        $base = trim($base, '_');
        $key  = $base;
        $n    = 1;
        while (Section::where('key', $key)->exists()) {
            $key = $base . '_' . $n++;
        }
        return $key;
    }
}
