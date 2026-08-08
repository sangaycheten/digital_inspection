<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): View
    {
        $permissionGroups = Permission::all()
            ->groupBy(fn ($p) => $p->module ?? 'Ungrouped')
            ->sortKeys();

        $modules = $permissionGroups->keys()->sort()->values();

        return view('admin.permissions.index', compact('permissionGroups', 'modules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:100', 'unique:permissions,name'],
            'module'     => ['required', 'string', 'max:100'],
            'new_module' => ['required_if:module,__new__', 'nullable', 'string', 'max:100'],
        ]);

        $module = $request->module === '__new__'
            ? trim($request->new_module)
            : trim($request->module);

        $permission = Permission::create([
            'name'       => strtolower(trim($request->name)),
            'guard_name' => 'web',
            'module'     => $module,
        ]);

        activity()
            ->causedBy(request()->user())
            ->performedOn($permission)
            ->event('created')
            ->withProperties(['attributes' => ['name' => $permission->name, 'module' => $permission->module]])
            ->log("Permission created: {$permission->name}");

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission \"{$permission->name}\" created successfully.");
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $name = $permission->name;

        activity()
            ->causedBy(request()->user())
            ->performedOn($permission)
            ->event('deleted')
            ->withProperties(['attributes' => ['name' => $name, 'module' => $permission->module]])
            ->log("Permission deleted: {$name}");

        $permission->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission \"{$name}\" deleted.");
    }
}
