<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Explicit rows to avoid any loop-variable interpolation issues
        $rows = [
            ['name' => 'view users',        'module' => 'Users'],
            ['name' => 'add users',         'module' => 'Users'],
            ['name' => 'edit users',        'module' => 'Users'],
            ['name' => 'delete users',      'module' => 'Users'],
            ['name' => 'view roles',        'module' => 'Roles'],
            ['name' => 'edit roles',        'module' => 'Roles'],
            ['name' => 'view permissions',  'module' => 'Permissions'],
            ['name' => 'add permissions',   'module' => 'Permissions'],
            ['name' => 'edit permissions',  'module' => 'Permissions'],
            ['name' => 'delete permissions','module' => 'Permissions'],
        ];

        foreach ($rows as $row) {
            if (!DB::table('permissions')->where('name', $row['name'])->where('guard_name', 'web')->exists()) {
                DB::table('permissions')->insert([
                    'name'       => $row['name'],
                    'guard_name' => 'web',
                    'module'     => $row['module'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Build newPerms for role assignment below
        $newPerms = [
            'Users'       => ['view users', 'add users', 'edit users', 'delete users'],
            'Roles'       => ['view roles', 'edit roles'],
            'Permissions' => ['view permissions', 'add permissions', 'edit permissions', 'delete permissions'],
        ];

        // Remove old flat permissions
        $oldPerms = ['manage users', 'assign roles', 'manage permissions'];
        foreach ($oldPerms as $name) {
            $perm = DB::table('permissions')->where('name', $name)->where('guard_name', 'web')->first();
            if ($perm) {
                DB::table('role_has_permissions')->where('permission_id', $perm->id)->delete();
                DB::table('model_has_permissions')->where('permission_id', $perm->id)->delete();
                DB::table('permissions')->where('id', $perm->id)->delete();
            }
        }

        // Assign new permissions to system-administrator and manager
        $sysAdminPerms = array_merge(...array_values($newPerms));
        $managerPerms  = ['view users', 'add users', 'edit users', 'delete users'];   // managers manage users only

        $rolePerms = [
            'system-administrator' => $sysAdminPerms,
            'manager'              => $managerPerms,
        ];

        foreach ($rolePerms as $roleName => $permNames) {
            $role = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->first();
            if (!$role) continue;
            foreach ($permNames as $permName) {
                $perm = DB::table('permissions')->where('name', $permName)->where('guard_name', 'web')->first();
                if ($perm) {
                    DB::table('role_has_permissions')->updateOrInsert([
                        'role_id'       => $role->id,
                        'permission_id' => $perm->id,
                    ]);
                }
            }
        }

        // Update MenuSequence children for user-role group (keep explicit labels so sidebar doesn't derive from permission name)
        DB::table('menu_sequences')
            ->where('role', 'system-administrator')->where('key', 'manage-users')->where('parent_key', 'user-role')
            ->update(['label' => 'Manage Users', 'permission' => 'view users', 'canany' => null]);

        DB::table('menu_sequences')
            ->where('role', 'system-administrator')->where('key', 'assign-roles')->where('parent_key', 'user-role')
            ->update(['label' => 'Assign Roles', 'permission' => 'view roles', 'canany' => null]);

        DB::table('menu_sequences')
            ->where('role', 'system-administrator')->where('key', 'permission')->where('parent_key', 'user-role')
            ->update(['label' => 'Manage Permissions', 'permission' => 'view permissions', 'canany' => null]);

        // Update parent canany
        DB::table('menu_sequences')
            ->where('role', 'system-administrator')->where('key', 'user-role')->whereNull('parent_key')
            ->update(['canany' => json_encode(['view users', 'view roles', 'view permissions'])]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $restore = [
            'manage users'      => 'User & Role Management',
            'assign roles'      => 'User & Role Management',
            'manage permissions'=> 'User & Role Management',
        ];

        foreach ($restore as $name => $module) {
            if (!DB::table('permissions')->where('name', $name)->where('guard_name', 'web')->exists()) {
                DB::table('permissions')->insert([
                    'name' => $name, 'guard_name' => 'web', 'module' => $module,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        $actionPerms = [
            'view users','add users','edit users','delete users',
            'view roles','edit roles',
            'view permissions','add permissions','edit permissions','delete permissions',
        ];
        foreach ($actionPerms as $name) {
            $perm = DB::table('permissions')->where('name', $name)->where('guard_name', 'web')->first();
            if ($perm) {
                DB::table('role_has_permissions')->where('permission_id', $perm->id)->delete();
                DB::table('permissions')->where('id', $perm->id)->delete();
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
