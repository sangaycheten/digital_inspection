<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * parent_key was not in $fillable when migration 000002 ran, so all seeded
     * child rows were created with parent_key = NULL. This migration repairs the
     * parent_key column and populates the content columns for those children.
     */
    public function up(): void
    {
        $children = [
            // Settings (user-role)
            ['parent_key' => 'user-role', 'key' => 'manage-users',  'permission' => 'manage users', 'icon' => 'ri-team-line',           'route' => 'admin.users.index',       'pattern' => 'admin.users.*'],
            ['parent_key' => 'user-role', 'key' => 'assign-roles',  'permission' => 'assign roles', 'icon' => 'ri-shield-keyhole-line', 'route' => 'admin.rbac.index',        'pattern' => 'admin.rbac.*'],
            ['parent_key' => 'user-role', 'key' => 'permission',    'permission' => 'permission',   'icon' => 'ri-key-2-line',          'route' => 'admin.permissions.index', 'pattern' => 'admin.permissions.*'],
            ['parent_key' => 'user-role', 'key' => 'menu-elements', 'label'      => 'Menu Elements','icon' => 'ri-menu-2-line',          'route' => 'admin.menu.index',        'pattern' => 'admin.menu.*'],

            // Manage Master (master-data)
            ['parent_key' => 'master-data', 'key' => 'clients',        'label' => 'Clients',        'icon' => 'ri-building-2-line',    'route' => 'admin.master.clients.index',     'pattern' => 'admin.master.clients.*'],
            ['parent_key' => 'master-data', 'key' => 'sites',          'label' => 'Sites',          'icon' => 'ri-map-pin-line',       'route' => 'admin.master.sites.index',       'pattern' => 'admin.master.sites.*'],
            ['parent_key' => 'master-data', 'key' => 'buildings',      'label' => 'Buildings',      'icon' => 'ri-home-office-line',   'route' => 'admin.master.buildings.index',   'pattern' => 'admin.master.buildings.*'],
            ['parent_key' => 'master-data', 'key' => 'reference-data', 'label' => 'Reference Data', 'icon' => 'ri-list-check-2',       'route' => 'admin.master.lookups.index',     'pattern' => 'admin.master.lookups.*'],
            ['parent_key' => 'master-data', 'key' => 'sections',       'label' => 'Sections',       'icon' => 'ri-layout-2-line',      'route' => 'admin.master.sections.index',    'pattern' => 'admin.master.sections.*'],
            ['parent_key' => 'master-data', 'key' => 'data-types',     'label' => 'Data Types',     'icon' => 'ri-list-settings-line', 'route' => 'admin.master.data-types.index', 'pattern' => 'admin.master.data-types.*'],
            ['parent_key' => 'master-data', 'key' => 'hierarchy',      'label' => 'Hierarchy',      'icon' => 'ri-git-branch-line',    'route' => 'admin.master.hierarchy.index',   'pattern' => 'admin.master.hierarchy.*'],

            // Jobs
            ['parent_key' => 'jobs', 'key' => 'all-jobs',   'permission' => 'view jobs',   'icon' => 'ri-list-unordered',         'route' => 'admin.jobs.index',  'pattern' => 'admin.jobs.index'],
            ['parent_key' => 'jobs', 'key' => 'create-job', 'permission' => 'manage jobs', 'icon' => 'ri-calendar-schedule-line', 'route' => 'admin.jobs.create', 'pattern' => 'admin.jobs.create'],

            // Assets
            ['parent_key' => 'assets', 'key' => 'all-assets',    'permission' => 'view assets', 'icon' => 'ri-list-check-3', 'route' => 'admin.assets.index', 'pattern' => 'admin.assets.index'],
            ['parent_key' => 'assets', 'key' => 'asset-history', 'label' => 'Asset History',   'icon' => 'ri-history-line',  'route' => 'admin.assets.index', 'route_params' => json_encode(['status' => 'fail']), 'pattern' => 'admin.assets.*', 'active_params' => json_encode(['status' => 'fail'])],

            // Inspections
            ['parent_key' => 'inspections', 'key' => 'all-inspections', 'permission' => 'review inspections', 'icon' => 'ri-list-unordered',       'route' => 'admin.inspections.index', 'pattern' => 'admin.inspections.index', 'no_params' => true],
            ['parent_key' => 'inspections', 'key' => 'pending-review',  'label' => 'Pending Review',         'icon' => 'ri-time-line',             'route' => 'admin.inspections.index', 'route_params' => json_encode(['status' => 'submitted']), 'pattern' => 'admin.inspections.index', 'active_params' => json_encode(['status' => 'submitted'])],
            ['parent_key' => 'inspections', 'key' => 'approved',        'label' => 'Approved',               'icon' => 'ri-checkbox-circle-line',  'route' => 'admin.inspections.index', 'route_params' => json_encode(['status' => 'approved']),  'pattern' => 'admin.inspections.index', 'active_params' => json_encode(['status' => 'approved'])],
        ];

        foreach ($children as $item) {
            $parentKey = $item['parent_key'];
            $key       = $item['key'];
            $data      = array_diff_key($item, ['parent_key' => 1, 'key' => 1]);
            $data['parent_key'] = $parentKey;
            $data['section']    = 'Operations'; // children inherit section from parent visually

            DB::table('menu_sequences')
                ->where('role', 'system-administrator')
                ->where('key', $key)
                ->whereNull('parent_key') // all children currently have NULL due to fillable bug
                ->update($data);
        }
    }

    public function down(): void
    {
        // Reset children back to NULL parent_key (reversing only parent_key and section)
        $childKeys = [
            'manage-users','assign-roles','permission','menu-elements',
            'clients','sites','buildings','reference-data','sections','data-types','hierarchy',
            'all-jobs','create-job',
            'all-assets','asset-history',
            'all-inspections','pending-review','approved',
        ];

        DB::table('menu_sequences')
            ->where('role', 'system-administrator')
            ->whereIn('key', $childKeys)
            ->update(['parent_key' => null]);
    }
};
