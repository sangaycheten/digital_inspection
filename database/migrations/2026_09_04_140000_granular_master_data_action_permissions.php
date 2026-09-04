<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Replace flat "section" permissions with action-level CRUD permissions.
     * Old: clients | sites | buildings | reference data | sections | data types | client assignments
     * New: view/add/edit/delete [section] — each section gets its own module group
     */
    public function up(): void
    {
        // New action-level permissions keyed by module name
        $newPerms = [
            'Clients' => [
                'view clients', 'add clients', 'edit clients', 'delete clients',
            ],
            'Sites' => [
                'view sites', 'add sites', 'edit sites', 'delete sites',
            ],
            'Buildings' => [
                'view buildings', 'add buildings', 'edit buildings', 'delete buildings',
            ],
            'Reference Data' => [
                'view reference data', 'add reference data', 'edit reference data', 'delete reference data',
            ],
            'Sections' => [
                'view sections', 'add sections', 'edit sections', 'delete sections',
            ],
            'Data Types' => [
                'view data types', 'add data types', 'edit data types', 'delete data types',
            ],
            'Client Assignments' => [
                'view client assignments', 'edit client assignments',
            ],
        ];

        // Create new permissions
        foreach ($newPerms as $module => $names) {
            foreach ($names as $name) {
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
        }

        // Remove old flat permissions from permissions table (not role_has_permissions — those cascade by id)
        $oldPerms = ['clients', 'sites', 'buildings', 'reference data', 'sections', 'data types', 'client assignments', 'manage master'];
        foreach ($oldPerms as $name) {
            $perm = DB::table('permissions')->where('name', $name)->where('guard_name', 'web')->first();
            if ($perm) {
                DB::table('role_has_permissions')->where('permission_id', $perm->id)->delete();
                DB::table('model_has_permissions')->where('permission_id', $perm->id)->delete();
                DB::table('permissions')->where('id', $perm->id)->delete();
            }
        }

        // Assign new permissions to roles
        $sysAdminPerms = array_merge(...array_values($newPerms));

        $managerPerms = array_merge(...array_values($newPerms));   // same as sysadmin for master data

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

        // Update MenuSequence: master-data parent gets canany with all view permissions
        $viewPerms = ['view clients', 'view sites', 'view buildings', 'view reference data', 'view sections', 'view data types', 'view client assignments'];
        DB::table('menu_sequences')
            ->where('role', 'system-administrator')
            ->where('key', 'master-data')
            ->whereNull('parent_key')
            ->update(['canany' => json_encode($viewPerms)]);

        // Update each child item: use the view permission for sidebar visibility
        $childViewPerms = [
            'clients'        => 'view clients',
            'sites'          => 'view sites',
            'buildings'      => 'view buildings',
            'reference-data' => 'view reference data',
            'sections'       => 'view sections',
            'data-types'     => 'view data types',
            'hierarchy'      => 'view client assignments',
        ];

        foreach ($childViewPerms as $key => $permission) {
            DB::table('menu_sequences')
                ->where('role', 'system-administrator')
                ->where('key', $key)
                ->where('parent_key', 'master-data')
                ->update(['permission' => $permission, 'canany' => null]);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Restore old flat permissions
        $restore = [
            'clients'            => 'Master Data',
            'sites'              => 'Master Data',
            'buildings'          => 'Master Data',
            'reference data'     => 'Master Data',
            'sections'           => 'Master Data',
            'data types'         => 'Master Data',
            'client assignments' => 'Master Data',
        ];

        foreach ($restore as $name => $module) {
            if (!DB::table('permissions')->where('name', $name)->where('guard_name', 'web')->exists()) {
                DB::table('permissions')->insert([
                    'name' => $name, 'guard_name' => 'web', 'module' => $module,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        // Delete new action-level permissions
        $actionPerms = [
            'view clients','add clients','edit clients','delete clients',
            'view sites','add sites','edit sites','delete sites',
            'view buildings','add buildings','edit buildings','delete buildings',
            'view reference data','add reference data','edit reference data','delete reference data',
            'view sections','add sections','edit sections','delete sections',
            'view data types','add data types','edit data types','delete data types',
            'view client assignments','edit client assignments',
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
