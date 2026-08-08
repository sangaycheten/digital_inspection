<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterLookup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterLookupController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->input('category');

        $lookups = MasterLookup::when($category, fn ($q) => $q->where('category', $category))
            ->orderBy('category')->orderBy('sort_order')->orderBy('label')
            ->paginate(20)->withQueryString();

        $grouped = MasterLookup::orderBy('sort_order')->orderBy('label')
            ->get()->groupBy('category');

        return view('admin.master.lookups.index', compact('lookups', 'grouped', 'category'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category'   => ['required', 'in:asset_type,defect_reason,recommendation'],
            'label'      => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $lookup = MasterLookup::create($data);

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($lookup)->event('created')
            ->withProperties(['attributes' => $data])
            ->log("Lookup created: [{$lookup->category}] {$lookup->label}");

        return redirect()->route('admin.master.lookups.index', ['category' => $data['category']])
            ->with('success', "Lookup \"{$lookup->label}\" added.");
    }

    public function update(Request $request, MasterLookup $lookup): RedirectResponse
    {
        $data = $request->validate([
            'category'   => ['required', 'in:asset_type,defect_reason,recommendation'],
            'label'      => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $lookup->update($data);

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($lookup)->event('updated')
            ->log("Lookup updated: [{$lookup->category}] {$lookup->label}");

        return redirect()->route('admin.master.lookups.index', ['category' => $data['category']])
            ->with('success', "Lookup \"{$lookup->label}\" updated.");
    }

    public function destroy(MasterLookup $lookup): RedirectResponse
    {
        $category = $lookup->category;
        $label    = $lookup->label;
        $lookup->delete();

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($lookup)->event('deleted')
            ->log("Lookup deleted: [{$category}] {$label}");

        return redirect()->route('admin.master.lookups.index', ['category' => $category])
            ->with('success', "Lookup \"{$label}\" deleted.");
    }
}
