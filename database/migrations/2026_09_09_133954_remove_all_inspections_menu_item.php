<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('menu_sequences')->where('key', 'all-inspections')->delete();
    }

    public function down(): void
    {
        DB::table('menu_sequences')->insert([
            'id'          => '01a0554d-4954-7145-abb1-242d25b63389',
            'role'        => 'system-administrator',
            'section'     => 'Operations',
            'key'         => 'all-inspections',
            'parent_key'  => 'inspections',
            'sequence'    => 1,
            'label'       => null,
            'icon'        => 'ri-list-unordered',
            'route'       => 'admin.inspections.index',
            'route_params'=> null,
            'pattern'     => 'admin.inspections.index',
            'permission'  => 'review inspections',
            'canany'      => null,
            'active_params'=> null,
            'no_params'   => 1,
            'collapse_id' => null,
            'enabled'     => 1,
        ]);
    }
};
