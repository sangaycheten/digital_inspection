<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Remove stale roles from old seeder runs
        Role::whereIn('name', ['admin', 'user', 'reviewer-approver'])->delete();

        $permissions = [
            'Jobs'                         => ['view jobs', 'manage jobs', 'assign technicians'],
            'Inspections'                  => ['capture inspections', 'review inspections'],
            'Re-Inspections'               => ['capture reinspections', 'review reinspections'],
            'Installation & Rectification' => ['capture installations', 'review installations'],
            'Asset Register'               => ['view assets', 'manage assets'],
            'Documents'                    => ['view documents', 'manage documents', 'approve documents', 'send documents', 'download documents'],
            'Client Portal'                => ['access client portal'],
            'Clients'            => ['view clients', 'add clients', 'edit clients', 'delete clients'],
            'Sites'              => ['view sites', 'add sites', 'edit sites', 'delete sites'],
            'Buildings'          => ['view buildings', 'add buildings', 'edit buildings', 'delete buildings'],
            'Reference Data'     => ['view reference data', 'add reference data', 'edit reference data', 'delete reference data'],
            'Sections'           => ['view sections', 'add sections', 'edit sections', 'delete sections'],
            'Data Types'         => ['view data types', 'add data types', 'edit data types', 'delete data types'],
            'Client Assignments' => ['view client assignments', 'edit client assignments'],
            'Users'       => ['view users', 'add users', 'edit users', 'delete users'],
            'Roles'       => ['view roles', 'edit roles'],
            'Permissions' => ['view permissions', 'add permissions', 'edit permissions', 'delete permissions'],
            'Questionnaires' => ['view questionnaires', 'add questionnaires', 'edit questionnaires', 'delete questionnaires'],
            'Audit Log'      => ['view audit log'],
            'Export'                       => ['export records', 'export client records'],
        ];

        foreach ($permissions as $module => $names) {
            foreach ($names as $name) {
                $perm = Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['module' => $module]
                );
                if ($perm->module !== $module) {
                    $perm->update(['module' => $module]);
                }
            }
        }

        $systemAdmin     = Role::firstOrCreate(['name' => 'system-administrator', 'guard_name' => 'web']);
        $manager         = Role::firstOrCreate(['name' => 'manager',              'guard_name' => 'web']);
        $fieldTechnician = Role::firstOrCreate(['name' => 'field-technician',     'guard_name' => 'web']);
        $clientUser      = Role::firstOrCreate(['name' => 'client-user',          'guard_name' => 'web']);

        $systemAdmin->syncPermissions([
            'view jobs', 'manage jobs', 'assign technicians',
            'review inspections', 'review reinspections', 'review installations',
            'view assets', 'manage assets',
            'view documents', 'manage documents', 'approve documents', 'send documents', 'download documents',
            'view clients', 'add clients', 'edit clients', 'delete clients',
            'view sites', 'add sites', 'edit sites', 'delete sites',
            'view buildings', 'add buildings', 'edit buildings', 'delete buildings',
            'view reference data', 'add reference data', 'edit reference data', 'delete reference data',
            'view sections', 'add sections', 'edit sections', 'delete sections',
            'view data types', 'add data types', 'edit data types', 'delete data types',
            'view client assignments', 'edit client assignments',
            'view users', 'add users', 'edit users', 'delete users',
            'view roles', 'edit roles',
            'view permissions', 'add permissions', 'edit permissions', 'delete permissions',
            'view questionnaires', 'add questionnaires', 'edit questionnaires', 'delete questionnaires',
            'view audit log', 'export records',
        ]);

        $manager->syncPermissions([
            'view jobs', 'manage jobs', 'assign technicians',
            'review inspections', 'review reinspections', 'review installations',
            'view assets', 'manage assets',
            'view documents', 'manage documents', 'approve documents', 'send documents', 'download documents',
            'view clients', 'add clients', 'edit clients', 'delete clients',
            'view sites', 'add sites', 'edit sites', 'delete sites',
            'view buildings', 'add buildings', 'edit buildings', 'delete buildings',
            'view reference data', 'add reference data', 'edit reference data', 'delete reference data',
            'view sections', 'add sections', 'edit sections', 'delete sections',
            'view data types', 'add data types', 'edit data types', 'delete data types',
            'view client assignments', 'edit client assignments',
            'view users', 'add users', 'edit users', 'delete users',
            'view audit log', 'export records',
        ]);

        $fieldTechnician->syncPermissions([
            'view jobs',
            'capture inspections', 'capture reinspections', 'capture installations',
            'view assets', 'view documents',
        ]);

        $clientUser->syncPermissions([
            'access client portal',
            'view assets', 'view documents', 'download documents',
            'export client records',
        ]);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'System Administrator',
                'password'          => bcrypt('Admin@12345'),
                'email_verified_at' => Carbon::now(),
            ]
        );
        $adminUser->syncRoles([$systemAdmin]);
    }
}
