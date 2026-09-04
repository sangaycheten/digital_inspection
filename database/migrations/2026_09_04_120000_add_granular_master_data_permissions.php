<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $masterPerms = [
            'clients'           => 'Master Data',
            'sites'             => 'Master Data',
            'buildings'         => 'Master Data',
            'reference data'    => 'Master Data',
            'sections'          => 'Master Data',
            'data types'        => 'Master Data',
            'client assignments'=> 'Master Data',
        ];

        // Create permissions that don't exist yet
        foreach ($masterPerms as $name => $module) {
            if (!DB::table('permissions')->where('name', $name)->where('guard_name', 'web')->exists()) {
                DB::table('permissions')->insert([
                    'name'       => $name,
                    'guard_name' => 'web',
                    'module'     => $module,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Assign to system-administrator and manager roles
        $rolesToAssign = ['system-administrator', 'manager'];
        foreach ($rolesToAssign as $roleName) {
            $role = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->first();
            if (!$role) continue;
            foreach (array_keys($masterPerms) as $permName) {
                $perm = DB::table('permissions')->where('name', $permName)->where('guard_name', 'web')->first();
                if ($perm) {
                    DB::table('role_has_permissions')->updateOrInsert([
                        'role_id'       => $role->id,
                        'permission_id' => $perm->id,
                    ]);
                }
            }
        }

        // Update master-data parent: replace single permission with canany array
        DB::table('menu_sequences')
            ->where('role', 'system-administrator')
            ->where('key', 'master-data')
            ->whereNull('parent_key')
            ->update([
                'label'      => 'Manage Master',
                'permission' => null,
                'canany'     => json_encode(array_keys($masterPerms)),
            ]);

        // Update each child item with its specific permission
        $childPermissions = [
            'clients'        => 'clients',
            'sites'          => 'sites',
            'buildings'      => 'buildings',
            'reference-data' => 'reference data',
            'sections'       => 'sections',
            'data-types'     => 'data types',
            'hierarchy'      => 'client assignments',
        ];

        foreach ($childPermissions as $key => $permission) {
            DB::table('menu_sequences')
                ->where('role', 'system-administrator')
                ->where('key', $key)
                ->where('parent_key', 'master-data')
                ->update(['permission' => $permission]);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Revert master-data parent back to single manage master permission
        DB::table('menu_sequences')
            ->where('role', 'system-administrator')
            ->where('key', 'master-data')
            ->whereNull('parent_key')
            ->update([
                'permission' => 'manage master',
                'canany'     => null,
            ]);

        // Clear individual permissions from child items
        $childKeys = ['clients', 'sites', 'buildings', 'reference-data', 'sections', 'data-types', 'hierarchy'];
        DB::table('menu_sequences')
            ->where('role', 'system-administrator')
            ->whereIn('key', $childKeys)
            ->where('parent_key', 'master-data')
            ->update(['permission' => null]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
