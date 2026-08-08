<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RbacController extends Controller
{
    public function index(): View
    {
        $roles = Role::with(['permissions', 'users'])->get();

        $permissionGroups = Permission::all()
            ->groupBy(fn ($p) => $p->module ?? 'Ungrouped')
            ->sortKeys();

        return view('admin.rbac.index', compact('roles', 'permissionGroups'));
    }

    public function update(Request $request): RedirectResponse
    {
        $roles = Role::with('permissions')->get();

        $masterPerms = Permission::where('module', 'Master')->pluck('name')->toArray();

        foreach ($roles as $role) {
            $newPermissions = $request->input("permissions.{$role->id}", []);

            if ($role->name === 'system-administrator') {
                // Always keep all Master permissions on system-administrator
                $newPermissions = array_values(array_unique(array_merge($newPermissions, $masterPerms)));
            } else {
                // Strip any Master permissions from all other roles
                $newPermissions = array_values(array_diff($newPermissions, $masterPerms));
            }

            $oldPermissions = $role->permissions->pluck('name')->toArray();

            $added   = array_values(array_diff($newPermissions, $oldPermissions));
            $removed = array_values(array_diff($oldPermissions, $newPermissions));

            $role->syncPermissions($newPermissions);

            if (count($added) || count($removed)) {
                activity()
                    ->causedBy(request()->user())
                    ->performedOn($role)
                    ->event('updated')
                    ->withProperties([
                        'old'        => ['permissions' => $oldPermissions],
                        'attributes' => ['permissions' => $newPermissions],
                        'added'      => $added,
                        'removed'    => $removed,
                    ])
                    ->log("Permissions updated for role: {$role->name}");
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.rbac.index')
            ->with('success', 'Role permissions updated successfully.');
    }


}
