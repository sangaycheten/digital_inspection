<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HierarchyController extends Controller
{
    public function index(): View
    {
        $clients  = Client::with(['manager', 'sites.buildings'])->orderBy('name')->get();
        $managers = User::role('manager')->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.master.hierarchy.index', compact('clients', 'managers'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $request->validate([
            'manager_id' => ['nullable', 'uuid', 'exists:users,id'],
        ]);

        $client->update(['manager_id' => $data['manager_id'] ?: null]);

        $managerName = $data['manager_id']
            ? User::find($data['manager_id'])?->name
            : 'None';

        return back()->with('success', "Manager for {$client->name} set to {$managerName}.");
    }
}
