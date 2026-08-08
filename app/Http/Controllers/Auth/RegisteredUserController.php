<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $role   = $request->input('role');

        $users = User::with('roles', 'creator', 'editor', 'client', 'sites.client')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($q) use ($role) {
                $q->whereHas('roles', fn ($q2) => $q2->where('name', $role));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $trashed = User::with('roles', 'creator')->onlyTrashed()->latest('deleted_at')->get();

        return view('admin.users.index', compact('users', 'trashed'));
    }

    public function create(): View
    {
        $roles   = Role::orderBy('name')->get();
        $clients = Client::where('status', 'active')->orderBy('name')->get();
        $sites   = Site::with('client:id,name,custom_client_code')->orderBy('address')->get(['id', 'client_id', 'address']);

        return view('admin.users.create', compact('roles', 'clients', 'sites'));
    }

    public function store(Request $request): RedirectResponse
    {
        $needsSites = in_array($request->input('role'), ['client-user', 'field-technician']);

        $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'role'       => ['required', 'string', 'exists:roles,name'],
            'client_id'  => ['required_if:role,client-user', 'nullable', 'exists:clients,id'],
            'site_ids'   => $needsSites ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'site_ids.*' => $request->role === 'client-user' && $request->client_id
                                ? [Rule::exists('sites', 'id')->where('client_id', $request->client_id)]
                                : ['exists:sites,id'],
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'email_verified_at' => Carbon::now(),
            'client_id'         => $request->role === 'client-user' ? $request->client_id : null,
            'created_by'        => request()->user()?->id,
            'updated_by'        => request()->user()?->id,
        ]);

        $user->assignRole($request->role);

        $loggedSites = [];
        if ($needsSites) {
            $user->sites()->sync($request->site_ids ?? []);
            $loggedSites = Site::whereIn('id', $request->site_ids ?? [])->pluck('address')->toArray();
        }

        $logProps = ['name' => $user->name, 'email' => $user->email, 'role' => $request->role];
        if ($needsSites) {
            $logProps['sites'] = $loggedSites;
        }
        if ($request->role === 'client-user') {
            $logProps['client_id'] = $request->client_id;
        }

        activity()->useLog('user')
            ->causedBy(request()->user())
            ->performedOn($user)
            ->event('created')
            ->withProperties(['attributes' => $logProps])
            ->log("User created: {$user->name}");

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} created successfully.");
    }

    public function edit(User $user): View
    {
        $roles       = Role::orderBy('name')->get();
        $clients     = Client::where('status', 'active')->orderBy('name')->get();
        $sites       = Site::with('client:id,name,custom_client_code')->orderBy('address')->get(['id', 'client_id', 'address']);
        $userSiteIds = $user->sites->pluck('id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'clients', 'sites', 'userSiteIds'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $needsSites = in_array($request->input('role'), ['client-user', 'field-technician']);

        $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'role'       => ['required', 'string', 'exists:roles,name'],
            'client_id'  => ['required_if:role,client-user', 'nullable', 'exists:clients,id'],
            'site_ids'   => $needsSites ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'site_ids.*' => $request->role === 'client-user' && $request->client_id
                                ? [Rule::exists('sites', 'id')->where('client_id', $request->client_id)]
                                : ['exists:sites,id'],
            'password'   => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $oldRole     = $user->roles->first()?->name;
        $newRole     = $request->role;
        $oldSiteIds  = $user->sites->pluck('id')->sort()->values()->toArray();
        $newSiteIds  = collect($request->site_ids ?? [])->map(fn($id) => (int)$id)->sort()->values()->toArray();
        $changes     = [];
        $oldProps    = [];

        if ($user->name !== $request->name) {
            $oldProps['name'] = $user->name;
            $changes['name']  = $request->name;
        }
        if ($user->email !== $request->email) {
            $oldProps['email'] = $user->email;
            $changes['email']  = $request->email;
        }
        if ($oldRole !== $newRole) {
            $oldProps['role'] = $oldRole;
            $changes['role']  = $newRole;
        }
        if ($request->filled('password')) {
            $changes['password'] = '(changed)';
        }
        if ($needsSites && $oldSiteIds !== $newSiteIds) {
            $oldProps['sites'] = Site::whereIn('id', $oldSiteIds)->pluck('address')->toArray();
            $changes['sites']  = Site::whereIn('id', $newSiteIds)->pluck('address')->toArray();
        }
        if ($newRole === 'client-user' && (int)$user->client_id !== (int)$request->client_id) {
            $oldProps['client_id'] = $user->client_id;
            $changes['client_id']  = $request->client_id;
        }

        $user->update([
            'name'       => $request->name,
            'email'      => $request->email,
            'client_id'  => $request->role === 'client-user' ? $request->client_id : null,
            'updated_by' => request()->user()?->id,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$newRole]);

        if ($needsSites) {
            $user->sites()->sync($request->site_ids ?? []);
        } else {
            $user->sites()->detach();
        }

        if (count($changes)) {
            activity()->useLog('user')
                ->causedBy(request()->user())
                ->performedOn($user)
                ->event('updated')
                ->withProperties([
                    'old'        => $oldProps,
                    'attributes' => $changes,
                ])
                ->log("User updated: {$user->name}");
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} updated successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === request()->user()?->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;

        activity()->useLog('user')
            ->causedBy(request()->user())
            ->performedOn($user)
            ->event('deleted')
            ->withProperties([
                'attributes' => ['name' => $user->name, 'email' => $user->email],
            ])
            ->log("User deleted: {$name}");

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$name} deleted successfully.");
    }

    public function restore(int $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        activity()->useLog('user')
            ->causedBy(request()->user())
            ->performedOn($user)
            ->event('restored')
            ->withProperties([
                'attributes' => ['name' => $user->name, 'email' => $user->email],
            ])
            ->log("User restored: {$user->name}");

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} restored successfully.");
    }
}
