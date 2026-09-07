<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename permission 'permissions' → 'manage permissions'
        DB::table('permissions')
            ->where('name', 'permissions')
            ->where('guard_name', 'web')
            ->update(['name' => 'manage permissions', 'module' => 'User & Role Management']);

        // Ensure 'manage permissions' exists (idempotent)
        if (!DB::table('permissions')->where('name', 'manage permissions')->where('guard_name', 'web')->exists()) {
            DB::table('permissions')->insert([
                'id'         => (string) Str::uuid(),
                'name'       => 'manage permissions',
                'guard_name' => 'web',
                'module'     => 'User & Role Management',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Assign manage permissions to system-administrator
        $adminRole = DB::table('roles')->where('name', 'system-administrator')->where('guard_name', 'web')->first();
        $perm      = DB::table('permissions')->where('name', 'manage permissions')->where('guard_name', 'web')->first();
        if ($adminRole && $perm) {
            DB::table('role_has_permissions')->updateOrInsert([
                'role_id'       => $adminRole->id,
                'permission_id' => $perm->id,
            ]);
        }

        // 3. Fix sidebar group: correct label, icon, canany
        DB::table('menu_sequences')
            ->where('role', 'system-administrator')
            ->where('key', 'user-role')
            ->whereNull('parent_key')
            ->update([
                'label'  => 'User & Role Management',
                'icon'   => 'ri-shield-user-line',
                'canany' => json_encode(['manage users', 'assign roles', 'manage permissions']),
            ]);

        // 4. Fix child item: correct permission name and label
        DB::table('menu_sequences')
            ->where('role', 'system-administrator')
            ->where('key', 'permission')
            ->update([
                'permission' => 'manage permissions',
                'label'      => 'Manage Permissions',
            ]);

        // 5. Clear Spatie permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::table('permissions')
            ->where('name', 'manage permissions')
            ->where('guard_name', 'web')
            ->update(['name' => 'permissions']);

        DB::table('menu_sequences')
            ->where('role', 'system-administrator')
            ->where('key', 'user-role')
            ->update([
                'label'  => 'Settings',
                'icon'   => 'ri-settings-3-line',
                'canany' => json_encode(['manage users', 'assign roles', 'permission']),
            ]);

        DB::table('menu_sequences')
            ->where('role', 'system-administrator')
            ->where('key', 'permission')
            ->update(['permission' => 'permission', 'label' => null]);
    }
};
