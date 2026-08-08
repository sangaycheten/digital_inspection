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
            'User & Role Management'       => ['manage users', 'assign roles', 'manage templates', 'configure system'],
            'Audit Log'                    => ['view audit log'],
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
            'manage users', 'assign roles', 'manage templates', 'configure system',
            'view audit log', 'export records',
        ]);

        $manager->syncPermissions([
            'view jobs', 'manage jobs', 'assign technicians',
            'review inspections', 'review reinspections', 'review installations',
            'view assets', 'manage assets',
            'view documents', 'manage documents', 'approve documents', 'send documents', 'download documents',
            'manage users', 'manage templates',
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
