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

        $moduleOrder  = config('navigation.module_order');
        $moduleLabels = config('navigation.module_labels');

        $permissionGroups = Permission::all()
            ->groupBy(fn ($p) => $p->module ?? 'Ungrouped')
            ->sortBy(fn ($_, $module) => ($pos = array_search($module, $moduleOrder)) !== false ? $pos : 999);

        return view('admin.rbac.index', compact('roles', 'permissionGroups', 'moduleLabels'));
    }

    // Permissions in these modules can only be held by system-administrator or manager
    private const RESTRICTED_MODULES = ['Master Data', 'User & Role Management', 'Audit Log'];

    public function update(Request $request): RedirectResponse
    {
        $roles = Role::with('permissions')->get();

        $restrictedPerms = Permission::whereIn('module', self::RESTRICTED_MODULES)->pluck('name')->toArray();

        foreach ($roles as $role) {
            $newPermissions = $request->input("permissions.{$role->id}", []);

            if (in_array($role->name, ['system-administrator', 'manager'])) {
                // Allowed roles: keep whatever is submitted for restricted modules
                $newPermissions = array_values($newPermissions);
            } else {
                // Other roles: strip all restricted-module permissions regardless of what was submitted
                $newPermissions = array_values(array_diff($newPermissions, $restrictedPerms));
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
