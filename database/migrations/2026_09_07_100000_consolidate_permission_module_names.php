<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        // Consolidate User & Role Management modules
        DB::table('permissions')
            ->whereIn('module', ['Users', 'Roles', 'Permissions'])
            ->update(['module' => 'User & Role Management']);

        // Consolidate Manage Master modules
        DB::table('permissions')
            ->whereIn('module', ['Clients', 'Sites', 'Buildings', 'Reference Data', 'Sections', 'Data Types', 'Client Assignments'])
            ->update(['module' => 'Manage Master']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $map = [
            'view users'              => 'Users',
            'add users'               => 'Users',
            'edit users'              => 'Users',
            'delete users'            => 'Users',
            'view roles'              => 'Roles',
            'edit roles'              => 'Roles',
            'view permissions'        => 'Permissions',
            'add permissions'         => 'Permissions',
            'edit permissions'        => 'Permissions',
            'delete permissions'      => 'Permissions',
            'view clients'            => 'Clients',
            'add clients'             => 'Clients',
            'edit clients'            => 'Clients',
            'delete clients'          => 'Clients',
            'view sites'              => 'Sites',
            'add sites'               => 'Sites',
            'edit sites'              => 'Sites',
            'delete sites'            => 'Sites',
            'view buildings'          => 'Buildings',
            'add buildings'           => 'Buildings',
            'edit buildings'          => 'Buildings',
            'delete buildings'        => 'Buildings',
            'view reference data'     => 'Reference Data',
            'add reference data'      => 'Reference Data',
            'edit reference data'     => 'Reference Data',
            'delete reference data'   => 'Reference Data',
            'view sections'           => 'Sections',
            'add sections'            => 'Sections',
            'edit sections'           => 'Sections',
            'delete sections'         => 'Sections',
            'view data types'         => 'Data Types',
            'add data types'          => 'Data Types',
            'edit data types'         => 'Data Types',
            'delete data types'       => 'Data Types',
            'view client assignments' => 'Client Assignments',
            'edit client assignments' => 'Client Assignments',
        ];

        foreach ($map as $permName => $module) {
            DB::table('permissions')->where('name', $permName)->update(['module' => $module]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
