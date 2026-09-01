<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuSequence;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
}
