<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $clients = Client::withCount('sites')->with('manager')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('custom_client_code', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(15)->withQueryString();

        $managers = User::role('manager')->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.master.clients.index', compact('clients', 'managers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'email'                => ['nullable', 'email', 'max:255', 'unique:clients,email'],
            'custom_client_code'   => ['required', 'string', 'max:20', 'unique:clients,custom_client_code'],
            'billing_contact_info' => ['nullable', 'string'],
            'status'               => ['required', 'in:active,inactive'],
            'manager_id'           => ['nullable', 'exists:users,id'],
            'logo'                 => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'site_name'            => ['required', 'string', 'max:255'],
            'site_timezone'        => ['required', 'string', 'timezone:all'],
            'site_address'         => ['nullable', 'string', 'max:500'],
            'site_latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'site_longitude'       => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('client-logos', 'public');
        } else {
            unset($data['logo']);
        }

        $client = Client::create($data);

        $site = Site::create([
            'client_id' => $client->id,
            'name'      => $data['site_name'],
            'timezone'  => $data['site_timezone'],
            'address'   => $data['site_address']   ?? null,
            'latitude'  => $data['site_latitude']  ?? null,
            'longitude' => $data['site_longitude'] ?? null,
        ]);

        activity()->useLog('master')->causedBy($request->user())
            ->performedOn($client)->event('created')
            ->log("Client created: {$client->name}");

        activity()->useLog('master')->causedBy($request->user())
            ->performedOn($site)->event('created')
            ->log("Site created: {$site->name} (for client {$client->name})");

        return redirect()->route('admin.master.clients.index')
            ->with('success', "Client \"{$client->name}\" created with site \"{$site->name}\" successfully.");
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'email'                => ['nullable', 'email', 'max:255', "unique:clients,email,{$client->id}"],
            'custom_client_code'   => ['required', 'string', 'max:20', "unique:clients,custom_client_code,{$client->id}"],
            'billing_contact_info' => ['nullable', 'string'],
            'status'               => ['required', 'in:active,inactive'],
            'manager_id'           => ['nullable', 'exists:users,id'],
            'logo'                 => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            if ($client->logo) {
                Storage::disk('public')->delete($client->logo);
            }
            $data['logo'] = $request->file('logo')->store('client-logos', 'public');
        } else {
            unset($data['logo']);
        }

        $client->update($data);

        activity()->useLog('master')->causedBy($request->user())
            ->performedOn($client)->event('updated')
            ->log("Client updated: {$client->name}");

        return redirect()->route('admin.master.clients.index')
            ->with('success', "Client \"{$client->name}\" updated successfully.");
    }

    public function destroy(Client $client): RedirectResponse
    {
        if ($client->logo) {
            Storage::disk('public')->delete($client->logo);
        }

        $name = $client->name;
        $client->delete();

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($client)->event('deleted')
            ->log("Client deleted: {$name}");

        return redirect()->route('admin.master.clients.index')
            ->with('success', "Client \"{$name}\" deleted.");
    }
}
