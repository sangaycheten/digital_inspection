<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuSequence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): View
    {
        $moduleOrder  = config('navigation.module_order');
        $moduleLabels = config('navigation.module_labels');

        $permissionGroups = Permission::all()
            ->groupBy(fn ($p) => $p->module ?? 'Ungrouped')
            ->sortBy(fn ($_, $module) => ($pos = array_search($module, $moduleOrder)) !== false ? $pos : 999);

        $modules = $permissionGroups->keys()->values();

        return view('admin.permissions.index', compact('permissionGroups', 'modules', 'moduleLabels'));
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

        // Auto-create a disabled menu item so the admin can enable and configure it
        $menuKey  = Str::slug($permission->name);
        $maxSeq   = MenuSequence::where('role', 'system-administrator')->whereNull('parent_key')->max('sequence') ?? 0;
        MenuSequence::firstOrCreate(
            ['role' => 'system-administrator', 'key' => $menuKey, 'parent_key' => null],
            ['section' => 'Operations', 'permission' => $permission->name, 'sequence' => $maxSeq + 1, 'enabled' => false]
        );

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission \"{$permission->name}\" created. A disabled menu item was added — enable it in Menu Elements when ready.");
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
