<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_sequences', function (Blueprint $table) {
            $table->string('section', 100)->default('Operations')->after('role');
            $table->string('label', 150)->nullable()->after('sequence');
            $table->string('icon', 100)->default('ri-circle-line')->after('label');
            $table->string('route', 150)->nullable()->after('icon');
            $table->json('route_params')->nullable()->after('route');
            $table->string('pattern', 150)->nullable()->after('route_params');
            $table->string('permission', 150)->nullable()->after('pattern');
            $table->json('canany')->nullable()->after('permission');
            $table->json('active_params')->nullable()->after('canany');
            $table->boolean('no_params')->default(false)->after('active_params');
            $table->string('collapse_id', 100)->nullable()->after('no_params');
            $table->boolean('enabled')->default(true)->after('collapse_id');
        });

        // Populate top-level rows for system-administrator
        $topLevel = [
            'dashboard'      => ['section' => 'Main',           'label' => 'Dashboard',     'icon' => 'ri-dashboard-2-line',  'route' => 'admin.dashboard'],
            'user-role'      => ['section' => 'Administration', 'label' => 'User & Role Management', 'icon' => 'ri-shield-user-line', 'canany' => json_encode(['manage users', 'assign roles', 'manage permissions']), 'collapse_id' => 'sidebarUserRole'],
            'audit-log'      => ['section' => 'Administration', 'icon' => 'ri-history-line',  'permission' => 'view audit log', 'route' => 'admin.audit-log.index',     'pattern' => 'admin.audit-log.*'],
            'master-data'    => ['section' => 'Administration', 'icon' => 'ri-database-2-line','permission' => 'manage master', 'collapse_id' => 'sidebarMaster'],
            'questionnaires' => ['section' => 'Administration', 'label' => 'Questionnaires', 'icon' => 'ri-questionnaire-line','route' => 'admin.questionnaires.index',  'pattern' => 'admin.questionnaires.*'],
            'jobs'           => ['section' => 'Operations',     'icon' => 'ri-briefcase-line','permission' => 'view jobs',      'collapse_id' => 'sidebarJobs'],
            'assets'         => ['section' => 'Operations',     'icon' => 'ri-tools-line',    'permission' => 'view assets',    'collapse_id' => 'sidebarAssets'],
            'inspections'    => ['section' => 'Operations',     'icon' => 'ri-survey-line',   'permission' => 'review inspections', 'collapse_id' => 'sidebarInspections', 'pattern' => 'admin.inspections.*'],
        ];

        foreach ($topLevel as $key => $data) {
            DB::table('menu_sequences')
                ->where('role', 'system-administrator')
                ->where('key', $key)
                ->whereNull('parent_key')
                ->update($data);
        }

        // Populate child rows for system-administrator
        $children = [
            // Settings (user-role)
            ['parent_key' => 'user-role', 'key' => 'manage-users',  'data' => ['permission' => 'manage users', 'icon' => 'ri-team-line',           'route' => 'admin.users.index',       'pattern' => 'admin.users.*']],
            ['parent_key' => 'user-role', 'key' => 'assign-roles',  'data' => ['permission' => 'assign roles', 'icon' => 'ri-shield-keyhole-line', 'route' => 'admin.rbac.index',        'pattern' => 'admin.rbac.*']],
            ['parent_key' => 'user-role', 'key' => 'permission',    'data' => ['permission' => 'manage permissions', 'label' => 'Manage Permissions', 'icon' => 'ri-key-2-line', 'route' => 'admin.permissions.index', 'pattern' => 'admin.permissions.*']],
            ['parent_key' => 'user-role', 'key' => 'menu-elements', 'data' => ['label' => 'Menu Elements',    'icon' => 'ri-menu-2-line',           'route' => 'admin.menu.index',        'pattern' => 'admin.menu.*']],

            // Manage Master (master-data)
            ['parent_key' => 'master-data', 'key' => 'clients',        'data' => ['label' => 'Clients',        'icon' => 'ri-building-2-line',    'route' => 'admin.master.clients.index',     'pattern' => 'admin.master.clients.*']],
            ['parent_key' => 'master-data', 'key' => 'sites',          'data' => ['label' => 'Sites',          'icon' => 'ri-map-pin-line',       'route' => 'admin.master.sites.index',       'pattern' => 'admin.master.sites.*']],
            ['parent_key' => 'master-data', 'key' => 'buildings',      'data' => ['label' => 'Buildings',      'icon' => 'ri-home-office-line',   'route' => 'admin.master.buildings.index',   'pattern' => 'admin.master.buildings.*']],
            ['parent_key' => 'master-data', 'key' => 'reference-data', 'data' => ['label' => 'Reference Data', 'icon' => 'ri-list-check-2',       'route' => 'admin.master.lookups.index',     'pattern' => 'admin.master.lookups.*']],
            ['parent_key' => 'master-data', 'key' => 'sections',       'data' => ['label' => 'Sections',       'icon' => 'ri-layout-2-line',      'route' => 'admin.master.sections.index',    'pattern' => 'admin.master.sections.*']],
            ['parent_key' => 'master-data', 'key' => 'data-types',     'data' => ['label' => 'Data Types',     'icon' => 'ri-list-settings-line', 'route' => 'admin.master.data-types.index', 'pattern' => 'admin.master.data-types.*']],
            ['parent_key' => 'master-data', 'key' => 'hierarchy',      'data' => ['label' => 'Hierarchy',      'icon' => 'ri-git-branch-line',    'route' => 'admin.master.hierarchy.index',   'pattern' => 'admin.master.hierarchy.*']],

            // Jobs
            ['parent_key' => 'jobs', 'key' => 'all-jobs',   'data' => ['permission' => 'view jobs',   'icon' => 'ri-list-unordered',         'route' => 'admin.jobs.index',  'pattern' => 'admin.jobs.index']],
            ['parent_key' => 'jobs', 'key' => 'create-job', 'data' => ['permission' => 'manage jobs', 'icon' => 'ri-calendar-schedule-line', 'route' => 'admin.jobs.create', 'pattern' => 'admin.jobs.create']],

            // Assets
            ['parent_key' => 'assets', 'key' => 'all-assets',    'data' => ['permission' => 'view assets', 'icon' => 'ri-list-check-3', 'route' => 'admin.assets.index', 'pattern' => 'admin.assets.index']],
            ['parent_key' => 'assets', 'key' => 'asset-history', 'data' => ['label' => 'Asset History',   'icon' => 'ri-history-line',  'route' => 'admin.assets.index', 'route_params' => json_encode(['status' => 'fail']), 'pattern' => 'admin.assets.*', 'active_params' => json_encode(['status' => 'fail'])]],

            // Inspections
            ['parent_key' => 'inspections', 'key' => 'all-inspections', 'data' => ['permission' => 'review inspections', 'icon' => 'ri-list-unordered',       'route' => 'admin.inspections.index', 'pattern' => 'admin.inspections.index', 'no_params' => true]],
            ['parent_key' => 'inspections', 'key' => 'pending-review',  'data' => ['label' => 'Pending Review',         'icon' => 'ri-time-line',             'route' => 'admin.inspections.index', 'route_params' => json_encode(['status' => 'submitted']), 'pattern' => 'admin.inspections.index', 'active_params' => json_encode(['status' => 'submitted'])]],
            ['parent_key' => 'inspections', 'key' => 'approved',        'data' => ['label' => 'Approved',               'icon' => 'ri-checkbox-circle-line',  'route' => 'admin.inspections.index', 'route_params' => json_encode(['status' => 'approved']),  'pattern' => 'admin.inspections.index', 'active_params' => json_encode(['status' => 'approved'])]],
        ];

        foreach ($children as $item) {
            DB::table('menu_sequences')
                ->where('role', 'system-administrator')
                ->where('parent_key', $item['parent_key'])
                ->where('key', $item['key'])
                ->update($item['data']);
        }
    }

    public function down(): void
    {
        Schema::table('menu_sequences', function (Blueprint $table) {
            $table->dropColumn([
                'section', 'label', 'icon', 'route', 'route_params',
                'pattern', 'permission', 'canany', 'active_params',
                'no_params', 'collapse_id', 'enabled',
            ]);
        });
    }
};
