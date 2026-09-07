<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuSequence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class MenuElementController extends Controller
{
    public function index(): View
    {
        $topLevel = MenuSequence::where('role', 'system-administrator')
            ->whereNull('parent_key')
            ->orderBy('sequence')
            ->get();

        // Load children grouped by parent_key
        $children = MenuSequence::where('role', 'system-administrator')
            ->whereNotNull('parent_key')
            ->orderBy('sequence')
            ->get()
            ->groupBy('parent_key');

        return view('admin.menu.index', compact('topLevel', 'children'));
    }

    public function moveUp(MenuSequence $item): RedirectResponse
    {
        $prev = MenuSequence::where('role', $item->role)
            ->where('parent_key', $item->parent_key)   // same level
            ->where('sequence', '<', $item->sequence)
            ->orderByDesc('sequence')
            ->first();

        if ($prev) {
            [$item->sequence, $prev->sequence] = [$prev->sequence, $item->sequence];
            $item->save();
            $prev->save();
        }

        return back()->with('success', "'{$item->label}' moved up.");
    }

    public function moveDown(MenuSequence $item): RedirectResponse
    {
        $next = MenuSequence::where('role', $item->role)
            ->where('parent_key', $item->parent_key)   // same level
            ->where('sequence', '>', $item->sequence)
            ->orderBy('sequence')
            ->first();

        if ($next) {
            [$item->sequence, $next->sequence] = [$next->sequence, $item->sequence];
            $item->save();
            $next->save();
        }

        return back()->with('success', "'{$item->label}' moved down.");
    }

    public function update(Request $request, MenuSequence $item): RedirectResponse
    {
        $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'icon'  => ['nullable', 'string', 'max:100'],
        ]);

        $oldLabel = $item->label;
        $newLabel = trim($request->label);

        $item->update([
            'label' => $newLabel,
            'icon'  => trim($request->input('icon') ?: $item->icon),
        ]);

        // Keep permission modules in sync: if any permissions use the old label
        // as their module name, rename them so the Permissions page stays consistent.
        $synced = 0;
        if ($oldLabel !== $newLabel) {
            $synced = Permission::where('module', $oldLabel)->update(['module' => $newLabel]);
            if ($synced > 0) {
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            }
        }

        $msg = "Menu item renamed from \"{$oldLabel}\" to \"{$newLabel}\".";
        if ($synced > 0) {
            $msg .= " ({$synced} permission module(s) updated too.)";
        }

        return back()->with('success', $msg);
    }

    public function destroy(MenuSequence $item): RedirectResponse
    {
        $label = $item->label;

        // Remove children first
        MenuSequence::where('role', $item->role)
            ->where('parent_key', $item->key)
            ->delete();

        $item->delete();

        return back()->with('success', "Menu item \"{$label}\" deleted.");
    }
}
