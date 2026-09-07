<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $renames = [
            'manage clients'        => 'clients',
            'manage sites'          => 'sites',
            'manage buildings'      => 'buildings',
            'manage reference data' => 'reference data',
            'manage sections'       => 'sections',
            'manage data types'     => 'data types',
            'manage hierarchy'      => 'client assignments',
        ];

        foreach ($renames as $old => $new) {
            DB::table('permissions')
                ->where('name', $old)
                ->where('guard_name', 'web')
                ->update(['name' => $new]);
        }

        // Update MenuSequence permission references
        $menuUpdates = [
            'clients'        => 'clients',
            'sites'          => 'sites',
            'buildings'      => 'buildings',
            'reference-data' => 'reference data',
            'sections'       => 'sections',
            'data-types'     => 'data types',
            'hierarchy'      => 'client assignments',
        ];

        foreach ($menuUpdates as $key => $permission) {
            DB::table('menu_sequences')
                ->where('role', 'system-administrator')
                ->where('key', $key)
                ->where('parent_key', 'master-data')
                ->update(['permission' => $permission]);
        }

        DB::table('menu_sequences')
            ->where('role', 'system-administrator')
            ->where('key', 'master-data')
            ->whereNull('parent_key')
            ->update([
                'canany' => json_encode(['clients', 'sites', 'buildings', 'reference data', 'sections', 'data types', 'client assignments']),
            ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $renames = [
            'clients'            => 'manage clients',
            'sites'              => 'manage sites',
            'buildings'          => 'manage buildings',
            'reference data'     => 'manage reference data',
            'sections'           => 'manage sections',
            'data types'         => 'manage data types',
            'client assignments' => 'manage hierarchy',
        ];

        foreach ($renames as $old => $new) {
            DB::table('permissions')
                ->where('name', $old)
                ->where('guard_name', 'web')
                ->update(['name' => $new]);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
