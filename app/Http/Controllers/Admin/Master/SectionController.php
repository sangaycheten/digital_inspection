<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
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
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('key', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.master.sections.index', compact('sections'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'key'         => ['required', 'string', 'max:100', 'alpha_dash', 'unique:sections,key'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'in:active,inactive'],
        ]);

        $data['key'] = strtolower($data['key']);

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
            'name'        => ['required', 'string', 'max:255'],
            'key'         => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('sections', 'key')->ignore($section->id)],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'in:active,inactive'],
        ]);

        $data['key'] = strtolower($data['key']);

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
}
