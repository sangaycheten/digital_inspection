<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterLookup;
use Illuminate\Http\JsonResponse;
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
            ->paginate(50)->withQueryString();

        $grouped = MasterLookup::orderBy('sort_order')->orderBy('label')
            ->get()->groupBy('category');

        return view('admin.master.lookups.index', compact('lookups', 'grouped', 'category'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'in:asset_type,defect_reason,recommendation'],
            'label'    => ['required', 'string', 'max:255'],
            'value'    => ['nullable', 'string', 'max:100', 'alpha_dash',
                           'unique:master_lookups,value,NULL,id,category,' . $request->category],
        ]);

        $data['sort_order'] = MasterLookup::where('category', $data['category'])->max('sort_order') + 1;

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
            'category' => ['required', 'in:asset_type,defect_reason,recommendation'],
            'label'    => ['required', 'string', 'max:255'],
            'value'    => ['nullable', 'string', 'max:100', 'alpha_dash',
                           'unique:master_lookups,value,' . $lookup->id . ',id,category,' . $request->category],
        ]);

        $lookup->update($data);

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($lookup)->event('updated')
            ->log("Lookup updated: [{$lookup->category}] {$lookup->label}");

        return redirect()->route('admin.master.lookups.index', ['category' => $data['category']])
            ->with('success', "Lookup \"{$lookup->label}\" updated.");
    }

    public function reorder(Request $request, MasterLookup $lookup): JsonResponse
    {
        $direction = $request->input('direction');

        if ($direction === 'up') {
            $swap = MasterLookup::where('category', $lookup->category)
                ->where('sort_order', '<', $lookup->sort_order)
                ->orderBy('sort_order', 'desc')
                ->first();
        } else {
            $swap = MasterLookup::where('category', $lookup->category)
                ->where('sort_order', '>', $lookup->sort_order)
                ->orderBy('sort_order')
                ->first();
        }

        if (!$swap) {
            return response()->json(['ok' => false]);
        }

        [$lookup->sort_order, $swap->sort_order] = [$swap->sort_order, $lookup->sort_order];
        $lookup->save();
        $swap->save();

        return response()->json(['ok' => true]);
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
