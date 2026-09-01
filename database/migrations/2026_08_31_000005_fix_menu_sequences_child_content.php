<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Set content columns for child menu items.
     * parent_key is already correct from migration 000004; just update by key.
     */
    public function up(): void
    {
        $children = [
            // Settings (user-role)
            ['key' => 'manage-users',  'permission' => 'manage users', 'icon' => 'ri-team-line',           'route' => 'admin.users.index',       'pattern' => 'admin.users.*'],
            ['key' => 'assign-roles',  'permission' => 'assign roles', 'icon' => 'ri-shield-keyhole-line', 'route' => 'admin.rbac.index',        'pattern' => 'admin.rbac.*'],
            ['key' => 'permission',    'permission' => 'permission',   'icon' => 'ri-key-2-line',           'route' => 'admin.permissions.index', 'pattern' => 'admin.permissions.*'],
            ['key' => 'menu-elements', 'label' => 'Menu Elements',     'icon' => 'ri-menu-2-line',          'route' => 'admin.menu.index',        'pattern' => 'admin.menu.*'],

            // Manage Master (master-data)
            ['key' => 'clients',        'label' => 'Clients',        'icon' => 'ri-building-2-line',    'route' => 'admin.master.clients.index',     'pattern' => 'admin.master.clients.*'],
            ['key' => 'sites',          'label' => 'Sites',          'icon' => 'ri-map-pin-line',       'route' => 'admin.master.sites.index',       'pattern' => 'admin.master.sites.*'],
            ['key' => 'buildings',      'label' => 'Buildings',      'icon' => 'ri-home-office-line',   'route' => 'admin.master.buildings.index',   'pattern' => 'admin.master.buildings.*'],
            ['key' => 'reference-data', 'label' => 'Reference Data', 'icon' => 'ri-list-check-2',       'route' => 'admin.master.lookups.index',     'pattern' => 'admin.master.lookups.*'],
            ['key' => 'sections',       'label' => 'Sections',       'icon' => 'ri-layout-2-line',      'route' => 'admin.master.sections.index',    'pattern' => 'admin.master.sections.*'],
            ['key' => 'data-types',     'label' => 'Data Types',     'icon' => 'ri-list-settings-line', 'route' => 'admin.master.data-types.index', 'pattern' => 'admin.master.data-types.*'],
            ['key' => 'hierarchy',      'label' => 'Hierarchy',      'icon' => 'ri-git-branch-line',    'route' => 'admin.master.hierarchy.index',   'pattern' => 'admin.master.hierarchy.*'],

            // Jobs
            ['key' => 'all-jobs',   'permission' => 'view jobs',   'icon' => 'ri-list-unordered',         'route' => 'admin.jobs.index',  'pattern' => 'admin.jobs.index'],
            ['key' => 'create-job', 'permission' => 'manage jobs', 'icon' => 'ri-calendar-schedule-line', 'route' => 'admin.jobs.create', 'pattern' => 'admin.jobs.create'],

            // Assets
            ['key' => 'all-assets',    'permission' => 'view assets', 'icon' => 'ri-list-check-3', 'route' => 'admin.assets.index', 'pattern' => 'admin.assets.index'],
            ['key' => 'asset-history', 'label' => 'Asset History',   'icon' => 'ri-history-line',  'route' => 'admin.assets.index', 'route_params' => json_encode(['status' => 'fail']), 'pattern' => 'admin.assets.*', 'active_params' => json_encode(['status' => 'fail'])],

            // Inspections
            ['key' => 'all-inspections', 'permission' => 'review inspections', 'icon' => 'ri-list-unordered',       'route' => 'admin.inspections.index', 'pattern' => 'admin.inspections.index', 'no_params' => true],
            ['key' => 'pending-review',  'label' => 'Pending Review',          'icon' => 'ri-time-line',             'route' => 'admin.inspections.index', 'route_params' => json_encode(['status' => 'submitted']), 'pattern' => 'admin.inspections.index', 'active_params' => json_encode(['status' => 'submitted'])],
            ['key' => 'approved',        'label' => 'Approved',                'icon' => 'ri-checkbox-circle-line',  'route' => 'admin.inspections.index', 'route_params' => json_encode(['status' => 'approved']),  'pattern' => 'admin.inspections.index', 'active_params' => json_encode(['status' => 'approved'])],
        ];

        foreach ($children as $item) {
            $key  = $item['key'];
            $data = array_diff_key($item, ['key' => 1]);

            DB::table('menu_sequences')
                ->where('role', 'system-administrator')
                ->where('key', $key)
                ->whereNotNull('parent_key')
                ->update($data);
        }
    }

    public function down(): void
    {
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
            ->update([
                'label' => null, 'permission' => null, 'icon' => 'ri-circle-line',
                'route' => null, 'route_params' => null, 'pattern' => null,
                'active_params' => null, 'no_params' => false,
            ]);
    }
};
