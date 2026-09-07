<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module display order for Permissions & RBAC pages
    |--------------------------------------------------------------------------
    | Keys are the DB `module` column values on permissions.
    | The label is derived from the first permission slug in each module,
    | so adding new permissions to a module requires no code change here.
    */
    // Maps DB module name → sidebar-matching display label for RBAC & Permissions pages
    'module_labels' => [
        'Settings'               => 'Settings',
        'User & Role Management' => 'User & Role Management',
        'Manage Master'          => 'Manage Master',
        'Audit Log'              => 'Audit Log',
        'Jobs'                   => 'Jobs',
        'Asset Register'         => 'Asset Register',
        'Documents'              => 'Documents',
        'Client Portal'          => 'Client Portal',
        'Inspections'            => 'Inspections',
        'Re-Inspections'         => 'Re-Inspections',
        'Installation & Rectification' => 'Installation & Rectification',
        'Export'                 => 'Export',
        'Reports'                => 'Reports',
    ],

    'module_order' => [
        'User & Role Management',
        'Manage Master',
        'Questionnaires',
        'Jobs',
        'Asset Register',
        'Documents',
        'Inspections',
        'Re-Inspections',
        'Installation & Rectification',
        'Client Portal',
        'Audit Log',
        'Export',
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation menus keyed by Spatie role name
    |--------------------------------------------------------------------------
    | Each role has an array of sections → items.
    |
    | Item keys:
    |   label       – explicit display text (omit to auto-derive from permission)
    |   permission  – single @can guard; label = Str::title(permission) if no label
    |   canany      – array of permissions for @canany guard; label from first the user holds
    |   icon        – Remix icon class
    |   route       – named route for href
    |   params      – route params array
    |   pattern     – routeIs() pattern for active-state detection
    |   active_params     – request query params that must also match for active state
    |   active_default_status – active when request('status', X) === X (default param)
    |   active_patterns   – extra routeIs() patterns that also make the parent active
    |   active_work_types – work_type values that make the parent active
    |   active_work_type  – single work_type value that makes a child active
    |   no_params   – true: active only when no query params present
    |   id          – collapse div id (collapsible items only)
    |   children    – array of child items (makes the item a collapsible group)
    */
    'menus' => [

        // ── Manager ───────────────────────────────────────────────────────
        'manager' => [
            [
                'section' => 'Main',
                'items'   => [
                    ['label' => 'Dashboard', 'icon' => 'ri-dashboard-2-line', 'route' => 'reviewer.dashboard'],
                ],
            ],
            [
                'section' => 'Administration',
                'items'   => [
                    [
                        'label'    => 'User & Role Management',
                        'icon'     => 'ri-shield-user-line',
                        'id'       => 'sidebarUserRoleR',
                        'canany'   => ['manage users', 'assign roles', 'manage permissions'],
                        'children' => [
                            ['permission' => 'manage users', 'icon' => 'ri-team-line',           'route' => 'admin.users.index',       'pattern' => 'admin.users.*'],
                            ['permission' => 'assign roles', 'icon' => 'ri-shield-keyhole-line', 'route' => 'admin.rbac.index',        'pattern' => 'admin.rbac.*'],
                            ['permission' => 'manage permissions', 'icon' => 'ri-key-2-line', 'route' => 'admin.permissions.index', 'pattern' => 'admin.permissions.*'],
                        ],
                    ],
                    [
                        'permission' => 'view audit log',
                        'icon'       => 'ri-history-line',
                        'route'      => 'admin.audit-log.index',
                        'pattern'    => 'admin.audit-log.*',
                    ],
                    [
                        'icon'    => 'ri-star-line',
                        'label'   => 'Client Feedback',
                        'route'   => 'admin.feedback.index',
                        'pattern' => 'admin.feedback.*',
                    ],
                    [
                        'permission' => 'manage master',
                        'icon'       => 'ri-database-2-line',
                        'id'         => 'sidebarMasterR',
                        'children'   => [
                            ['label' => 'Clients',        'icon' => 'ri-building-2-line',    'route' => 'admin.master.clients.index',     'pattern' => 'admin.master.clients.*'],
                            ['label' => 'Sites',          'icon' => 'ri-map-pin-line',       'route' => 'admin.master.sites.index',       'pattern' => 'admin.master.sites.*'],
                            ['label' => 'Buildings',      'icon' => 'ri-home-office-line',   'route' => 'admin.master.buildings.index',   'pattern' => 'admin.master.buildings.*'],
                            ['label' => 'Reference Data', 'icon' => 'ri-list-check-2',       'route' => 'admin.master.lookups.index',     'pattern' => 'admin.master.lookups.*'],
                            ['label' => 'Sections',       'icon' => 'ri-layout-2-line',      'route' => 'admin.master.sections.index',    'pattern' => 'admin.master.sections.*'],
                            ['label' => 'Data Types',     'icon' => 'ri-list-settings-line', 'route' => 'admin.master.data-types.index', 'pattern' => 'admin.master.data-types.*'],
                            ['label' => 'Client Assignments', 'icon' => 'ri-git-branch-line', 'route' => 'admin.master.hierarchy.index', 'pattern' => 'admin.master.hierarchy.*'],
                        ],
                    ],
                ],
            ],
            [
                'section' => 'Operations',
                'items'   => [
                    [
                        'permission' => 'view jobs',
                        'icon'       => 'ri-briefcase-line',
                        'id'         => 'sidebarJobsR',
                        'children'   => [
                            ['permission' => 'view jobs',   'icon' => 'ri-list-unordered',        'route' => 'admin.jobs.index',  'pattern' => 'admin.jobs.index'],
                            ['permission' => 'manage jobs', 'icon' => 'ri-calendar-schedule-line', 'route' => 'admin.jobs.create', 'pattern' => 'admin.jobs.create'],
                        ],
                    ],
                    [
                        'canany'   => ['review inspections', 'review reinspections', 'review installations'],
                        'icon'     => 'ri-survey-line',
                        'id'       => 'sidebarInspectionsR',
                        'pattern'  => 'reviewer.inspections.*',
                        'children' => [
                            ['permission' => 'review inspections', 'label' => 'Pending Review', 'icon' => 'ri-time-line',            'route' => 'reviewer.inspections.index', 'pattern' => 'reviewer.inspections.*', 'active_default_status' => 'submitted'],
                            ['permission' => 'review inspections', 'label' => 'Approved',       'icon' => 'ri-checkbox-circle-line', 'route' => 'reviewer.inspections.index', 'params' => ['status' => 'approved'],  'pattern' => 'reviewer.inspections.index', 'active_params' => ['status' => 'approved']],
                        ],
                    ],
                    [
                        'permission' => 'view assets',
                        'icon'       => 'ri-tools-line',
                        'id'         => 'sidebarAssetsR',
                        'children'   => [
                            ['permission' => 'view assets', 'icon' => 'ri-list-check-3', 'route' => 'admin.assets.index', 'pattern' => 'admin.assets.index'],
                            ['label' => 'Asset History',    'icon' => 'ri-history-line',  'route' => 'admin.assets.index', 'params' => ['status' => 'fail'], 'pattern' => 'admin.assets.*', 'active_params' => ['status' => 'fail']],
                        ],
                    ],
                ],
            ],
        ],

        // ── Field Technician ───────────────────────────────────────────────
        'field-technician' => [
            [
                'section' => 'Main',
                'items'   => [
                    ['label' => 'Dashboard', 'icon' => 'ri-dashboard-2-line', 'route' => 'technician.dashboard'],
                ],
            ],
            [
                'section' => 'My Work',
                'items'   => [
                    [
                        'permission' => 'view jobs',
                        'icon'       => 'ri-briefcase-line',
                        'route'      => 'technician.jobs.index',
                        'pattern'    => 'technician.jobs.*',
                    ],
                    [
                        'canany'           => ['capture inspections', 'capture reinspections', 'capture installations'],
                        'icon'             => 'ri-survey-line',
                        'id'               => 'sidebarCapture',
                        'active_patterns'  => ['technician.jobs.inspect*', 'technician.jobs.install*', 'technician.jobs.show'],
                        'active_work_types'=> ['first_inspection', 're_inspection', 'installation', 'rectification', 'combined'],
                        'children'         => [
                            ['permission' => 'capture inspections',  'route' => 'technician.jobs.index', 'params' => ['work_type' => 'first_inspection', 'status' => 'in_progress'], 'active_work_type' => 'first_inspection'],
                            ['permission' => 'capture reinspections', 'route' => 'technician.jobs.index', 'params' => ['work_type' => 're_inspection',     'status' => 'in_progress'], 'active_work_type' => 're_inspection'],
                            ['permission' => 'capture installations', 'route' => 'technician.jobs.index', 'params' => ['work_type' => 'installation',       'status' => 'in_progress'], 'active_work_type' => 'installation'],
                        ],
                    ],
                    [
                        'permission' => 'view assets',
                        'icon'       => 'ri-tools-line',
                        'route'      => 'admin.assets.index',
                        'pattern'    => 'admin.assets.*',
                    ],
                ],
            ],
        ],

        // ── Client User ────────────────────────────────────────────────────
        'client-user' => [
            [
                'section' => 'Main',
                'items'   => [
                    ['label' => 'Dashboard', 'icon' => 'ri-dashboard-2-line',  'route' => 'client.dashboard'],
                ],
            ],
            [
                'section' => 'My Portfolio',
                'items'   => [
                    ['label' => 'My Sites',   'icon' => 'ri-map-pin-line',  'route' => 'client.sites.index'],
                    ['label' => 'My Assets',  'icon' => 'ri-tools-line',    'route' => 'client.assets.index'],
                ],
            ],
            [
                'section' => 'Documents',
                'items'   => [
                    ['label' => 'Inspection Reports', 'icon' => 'ri-file-list-3-line', 'route' => 'client.reports.index'],
                ],
            ],
            [
                'section' => 'My Feedback',
                'items'   => [
                    ['label' => 'Feedback & Rating', 'icon' => 'ri-star-line', 'route' => 'client.feedback.index', 'pattern' => 'client.feedback.*'],
                ],
            ],
        ],

    ],

];
