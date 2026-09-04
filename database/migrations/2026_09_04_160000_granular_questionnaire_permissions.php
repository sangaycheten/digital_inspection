<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['name' => 'view questionnaires',   'module' => 'Questionnaires'],
            ['name' => 'add questionnaires',    'module' => 'Questionnaires'],
            ['name' => 'edit questionnaires',   'module' => 'Questionnaires'],
            ['name' => 'delete questionnaires', 'module' => 'Questionnaires'],
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

        // Assign all 4 to system-administrator
        $sysAdmin = DB::table('roles')->where('name', 'system-administrator')->where('guard_name', 'web')->first();
        foreach ($rows as $row) {
            $perm = DB::table('permissions')->where('name', $row['name'])->where('guard_name', 'web')->first();
            if ($sysAdmin && $perm) {
                DB::table('role_has_permissions')->updateOrInsert([
                    'role_id'       => $sysAdmin->id,
                    'permission_id' => $perm->id,
                ]);
            }
        }

        // Update MenuSequence for the questionnaires sidebar item
        DB::table('menu_sequences')
            ->where('role', 'system-administrator')
            ->where('key', 'questionnaires')
            ->update([
                'permission' => 'view questionnaires',
                'canany'     => null,
            ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $names = ['view questionnaires', 'add questionnaires', 'edit questionnaires', 'delete questionnaires'];
        foreach ($names as $name) {
            $perm = DB::table('permissions')->where('name', $name)->where('guard_name', 'web')->first();
            if ($perm) {
                DB::table('role_has_permissions')->where('permission_id', $perm->id)->delete();
                DB::table('permissions')->where('id', $perm->id)->delete();
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
