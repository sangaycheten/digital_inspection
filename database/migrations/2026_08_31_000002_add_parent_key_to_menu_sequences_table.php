<?php

use App\Models\MenuSequence;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_sequences', function (Blueprint $table) {
            $table->string('parent_key', 100)->nullable()->after('key');
            $table->dropUnique(['role', 'key']);
            $table->unique(['role', 'parent_key', 'key']);
        });

        // Seed sub-menu sequences for system-administrator
        $children = [
            // Settings (user-role)
            ['role' => 'system-administrator', 'parent_key' => 'user-role', 'key' => 'manage-users',  'sequence' => 1],
            ['role' => 'system-administrator', 'parent_key' => 'user-role', 'key' => 'assign-roles',  'sequence' => 2],
            ['role' => 'system-administrator', 'parent_key' => 'user-role', 'key' => 'permission',    'sequence' => 3],
            ['role' => 'system-administrator', 'parent_key' => 'user-role', 'key' => 'menu-elements', 'sequence' => 4],

            // Manage Master (master-data)
            ['role' => 'system-administrator', 'parent_key' => 'master-data', 'key' => 'clients',        'sequence' => 1],
            ['role' => 'system-administrator', 'parent_key' => 'master-data', 'key' => 'sites',          'sequence' => 2],
            ['role' => 'system-administrator', 'parent_key' => 'master-data', 'key' => 'buildings',      'sequence' => 3],
            ['role' => 'system-administrator', 'parent_key' => 'master-data', 'key' => 'reference-data', 'sequence' => 4],
            ['role' => 'system-administrator', 'parent_key' => 'master-data', 'key' => 'sections',       'sequence' => 5],
            ['role' => 'system-administrator', 'parent_key' => 'master-data', 'key' => 'data-types',     'sequence' => 6],
            ['role' => 'system-administrator', 'parent_key' => 'master-data', 'key' => 'hierarchy',      'sequence' => 7],

            // Jobs
            ['role' => 'system-administrator', 'parent_key' => 'jobs', 'key' => 'all-jobs',    'sequence' => 1],
            ['role' => 'system-administrator', 'parent_key' => 'jobs', 'key' => 'create-job',  'sequence' => 2],

            // Assets
            ['role' => 'system-administrator', 'parent_key' => 'assets', 'key' => 'all-assets',    'sequence' => 1],
            ['role' => 'system-administrator', 'parent_key' => 'assets', 'key' => 'asset-history', 'sequence' => 2],

            // Inspections
            ['role' => 'system-administrator', 'parent_key' => 'inspections', 'key' => 'all-inspections', 'sequence' => 1],
            ['role' => 'system-administrator', 'parent_key' => 'inspections', 'key' => 'pending-review',  'sequence' => 2],
            ['role' => 'system-administrator', 'parent_key' => 'inspections', 'key' => 'approved',        'sequence' => 3],
        ];

        foreach ($children as $row) {
            MenuSequence::create($row);
        }
    }

    public function down(): void
    {
        Schema::table('menu_sequences', function (Blueprint $table) {
            $table->dropUnique(['role', 'parent_key', 'key']);
            $table->dropColumn('parent_key');
            $table->unique(['role', 'key']);
        });
    }
};
