<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(Request $request): View
    {
        $sites = Site::with('client')
            ->when($request->search, fn ($q) => $q->where('address', 'like', "%{$request->search}%")
                                                   ->orWhere('name', 'like', "%{$request->search}%"))
            ->when($request->client_id, fn ($q) => $q->where('client_id', $request->client_id))
            ->latest()->paginate(15)->withQueryString();

        $clients = Client::orderBy('name')->get();

        return view('admin.master.sites.index', compact('sites', 'clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id'  => ['required', 'exists:clients,id'],
            'name'       => ['nullable', 'string', 'max:255'],
            'address'    => ['required', 'string'],
            'latitude'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'  => ['nullable', 'numeric', 'between:-180,180'],
            'site_notes' => ['nullable', 'string'],
        ]);

        $site = Site::create($data);

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($site)->event('created')
            ->withProperties(['attributes' => $data])
            ->log("Site created for client: {$site->client->name}");

        return redirect()->route('admin.master.sites.index')
            ->with('success', 'Site created successfully.');
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $data = $request->validate([
            'client_id'  => ['required', 'exists:clients,id'],
            'name'       => ['nullable', 'string', 'max:255'],
            'address'    => ['required', 'string'],
            'latitude'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'  => ['nullable', 'numeric', 'between:-180,180'],
            'site_notes' => ['nullable', 'string'],
        ]);

        $site->update($data);

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($site)->event('updated')
            ->log("Site #{$site->id} updated");

        return redirect()->route('admin.master.sites.index')
            ->with('success', 'Site updated successfully.');
    }

    public function destroy(Site $site): RedirectResponse
    {
        $site->delete();

        activity()->useLog('master')->causedBy(request()->user())
            ->performedOn($site)->event('deleted')
            ->log("Site #{$site->id} deleted");

        return redirect()->route('admin.master.sites.index')
            ->with('success', 'Site deleted.');
    }
}
