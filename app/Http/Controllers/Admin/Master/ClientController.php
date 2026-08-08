<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $clients = Client::withCount('sites')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('custom_client_code', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.master.clients.index', compact('clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'custom_client_code'   => ['required', 'string', 'max:20', 'unique:clients,custom_client_code'],
            'billing_contact_info' => ['nullable', 'string'],
            'status'               => ['required', 'in:active,inactive'],
        ]);

        $client = Client::create($data);

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($client)->event('created')
            ->withProperties(['attributes' => $data])
            ->log("Client created: {$client->name}");

        return redirect()->route('admin.master.clients.index')
            ->with('success', "Client \"{$client->name}\" created successfully.");
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'custom_client_code'   => ['required', 'string', 'max:20', "unique:clients,custom_client_code,{$client->id}"],
            'billing_contact_info' => ['nullable', 'string'],
            'status'               => ['required', 'in:active,inactive'],
        ]);

        $client->update($data);

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($client)->event('updated')
            ->log("Client updated: {$client->name}");

        return redirect()->route('admin.master.clients.index')
            ->with('success', "Client \"{$client->name}\" updated successfully.");
    }

    public function destroy(Client $client): RedirectResponse
    {
        $name = $client->name;
        $client->delete();

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($client)->event('deleted')
            ->log("Client deleted: {$name}");

        return redirect()->route('admin.master.clients.index')
            ->with('success', "Client \"{$name}\" deleted.");
    }
}
