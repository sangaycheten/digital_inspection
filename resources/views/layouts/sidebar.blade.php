<ul class="navbar-nav" id="navbar-nav">

    @php
        $userRoleMenu = [
            'manage users' => ['route' => 'admin.users.index',       'icon' => 'ri-team-line',           'pattern' => 'admin.users.*'],
            'assign roles' => ['route' => 'admin.rbac.index',        'icon' => 'ri-shield-keyhole-line', 'pattern' => 'admin.rbac.*'],
            'permission'   => ['route' => 'admin.permissions.index', 'icon' => 'ri-key-2-line',          'pattern' => 'admin.permissions.*'],
        ];
        $userRoleActive       = collect($userRoleMenu)->contains(fn($item) => request()->routeIs($item['pattern']));
        $auditLogActive       = request()->routeIs('admin.audit-log.*');
        $masterActive         = request()->routeIs('admin.master.*');
        $questionnairesActive = request()->routeIs('admin.questionnaires.*');
        $assetsActive         = request()->routeIs('admin.assets.*');
        $jobsActive           = request()->routeIs('admin.jobs.*');
        $inspectionsActive    = request()->routeIs('admin.inspections.*');
    @endphp

    {{-- ===================== SYSTEM ADMINISTRATOR ===================== --}}
    @role('system-administrator')

    <li class="menu-title"><span>Main</span></li>

    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
           href="{{ route('admin.dashboard') }}">
            <i class="ri-dashboard-2-line"></i><span>Dashboard</span>
        </a>
    </li>

    <li class="menu-title"><span>Administration</span></li>

    @canany(array_keys($userRoleMenu))
    <li class="nav-item">
        <a class="nav-link menu-link {{ $userRoleActive ? '' : 'collapsed' }}" href="#sidebarUserRole"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $userRoleActive ? 'true' : 'false' }}"
           aria-controls="sidebarUserRole">
            <i class="ri-settings-3-line"></i><span>User & Role Management</span>
        </a>
        <div class="collapse menu-dropdown {{ $userRoleActive ? 'show' : '' }}" id="sidebarUserRole">
            <ul class="nav nav-sm flex-column">
                @foreach($userRoleMenu as $permission => $item)
                @can($permission)
                <li class="nav-item">
                    <a href="{{ route($item['route']) }}"
                       class="nav-link {{ request()->routeIs($item['pattern']) ? 'active' : '' }}">
                        <i class="{{ $item['icon'] }}"></i> {{ Str::title($permission) }}
                    </a>
                </li>
                @endcan
                @endforeach
            </ul>
        </div>
    </li>
    @endcanany

    @can('view audit log')
    <li class="nav-item">
        <a class="nav-link menu-link {{ $auditLogActive ? 'active' : '' }}"
           href="{{ route('admin.audit-log.index') }}">
            <i class="ri-history-line"></i><span>{{ Str::title('view audit log') }}</span>
        </a>
    </li>
    @endcan

    @can('manage master')
    <li class="nav-item">
        <a class="nav-link menu-link {{ $masterActive ? '' : 'collapsed' }}" href="#sidebarMaster"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $masterActive ? 'true' : 'false' }}"
           aria-controls="sidebarMaster">
            <i class="ri-database-2-line"></i><span>{{ Str::title('manage master') }}</span>
        </a>
        <div class="collapse menu-dropdown {{ $masterActive ? 'show' : '' }}" id="sidebarMaster">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.master.clients.index') }}"
                       class="nav-link {{ request()->routeIs('admin.master.clients.*') ? 'active' : '' }}">
                        <i class="ri-building-2-line"></i> Clients
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.master.sites.index') }}"
                       class="nav-link {{ request()->routeIs('admin.master.sites.*') ? 'active' : '' }}">
                        <i class="ri-map-pin-line"></i> Sites
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.master.buildings.index') }}"
                       class="nav-link {{ request()->routeIs('admin.master.buildings.*') ? 'active' : '' }}">
                        <i class="ri-home-office-line"></i> Buildings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.master.lookups.index') }}"
                       class="nav-link {{ request()->routeIs('admin.master.lookups.*') ? 'active' : '' }}">
                        <i class="ri-list-check-2"></i> Reference Data
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.master.sections.index') }}"
                       class="nav-link {{ request()->routeIs('admin.master.sections.*') ? 'active' : '' }}">
                        <i class="ri-layout-2-line"></i> Sections
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.master.data-types.index') }}"
                       class="nav-link {{ request()->routeIs('admin.master.data-types.*') ? 'active' : '' }}">
                        <i class="ri-list-settings-line"></i> Data Types
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.master.hierarchy.index') }}"
                       class="nav-link {{ request()->routeIs('admin.master.hierarchy.*') ? 'active' : '' }}">
                        <i class="ri-git-branch-line"></i> Hierarchy
                    </a>
                </li>
            </ul>
        </div>
    </li>
    @endcan

    <li class="nav-item">
        <a class="nav-link menu-link {{ $questionnairesActive ? 'active' : '' }}"
           href="{{ route('admin.questionnaires.index') }}">
            <i class="ri-questionnaire-line"></i><span>Questionnaires</span>
        </a>
    </li>

    <li class="menu-title"><span>Operations</span></li>

    @can('view jobs')
    <li class="nav-item">
        <a class="nav-link menu-link {{ $jobsActive ? '' : 'collapsed' }}" href="#sidebarJobs"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $jobsActive ? 'true' : 'false' }}"
           aria-controls="sidebarJobs">
            <i class="ri-briefcase-line"></i><span>{{ Str::title('view jobs') }}</span>
        </a>
        <div class="collapse menu-dropdown {{ $jobsActive ? 'show' : '' }}" id="sidebarJobs">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.jobs.index') }}"
                       class="nav-link {{ request()->routeIs('admin.jobs.index') ? 'active' : '' }}">
                        <i class="ri-list-unordered"></i> All Jobs
                    </a>
                </li>
                @can('manage jobs')
                <li class="nav-item">
                    <a href="{{ route('admin.jobs.create') }}"
                       class="nav-link {{ request()->routeIs('admin.jobs.create') ? 'active' : '' }}">
                        <i class="ri-calendar-schedule-line"></i> Schedule Job
                    </a>
                </li>
                @endcan
            </ul>
        </div>
    </li>
    @endcan

    @can('view assets')
    <li class="nav-item">
        <a class="nav-link menu-link {{ $assetsActive ? '' : 'collapsed' }}" href="#sidebarAssets"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $assetsActive ? 'true' : 'false' }}"
           aria-controls="sidebarAssets">
            <i class="ri-tools-line"></i><span>{{ Str::title('view assets') }}</span>
        </a>
        <div class="collapse menu-dropdown {{ $assetsActive ? 'show' : '' }}" id="sidebarAssets">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.assets.index') }}"
                       class="nav-link {{ request()->routeIs('admin.assets.index') ? 'active' : '' }}">
                        <i class="ri-list-check-3"></i> All Assets
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.assets.index', ['status' => 'fail']) }}"
                       class="nav-link {{ request()->routeIs('admin.assets.*') && request('status') === 'fail' ? 'active' : '' }}">
                        <i class="ri-history-line"></i> Asset History
                    </a>
                </li>
            </ul>
        </div>
    </li>
    @endcan

    @can('review inspections')
    <li class="nav-item">
        <a class="nav-link menu-link {{ $inspectionsActive ? '' : 'collapsed' }}" href="#sidebarInspections"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $inspectionsActive ? 'true' : 'false' }}"
           aria-controls="sidebarInspections">
            <i class="ri-survey-line"></i><span>Inspections</span>
        </a>
        <div class="collapse menu-dropdown {{ $inspectionsActive ? 'show' : '' }}" id="sidebarInspections">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.inspections.index') }}"
                       class="nav-link {{ request()->routeIs('admin.inspections.index') && !request('status') ? 'active' : '' }}">
                        <i class="ri-list-unordered"></i> All Inspections
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.inspections.index', ['status' => 'submitted']) }}"
                       class="nav-link {{ request()->routeIs('admin.inspections.index') && request('status') === 'submitted' ? 'active' : '' }}">
                        <i class="ri-time-line me-1"></i>Pending Review
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.inspections.index', ['status' => 'approved']) }}"
                       class="nav-link {{ request()->routeIs('admin.inspections.index') && request('status') === 'approved' ? 'active' : '' }}">
                        <i class="ri-checkbox-circle-line me-1"></i>Approved
                    </a>
                </li>
            </ul>
        </div>
    </li>
    @endcan

    @endrole

    {{-- ===================== REVIEWER / APPROVER (MANAGER) ===================== --}}
    @role('manager')

    <li class="menu-title"><span>Main</span></li>

    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('reviewer.dashboard') ? 'active' : '' }}"
           href="{{ route('reviewer.dashboard') }}">
            <i class="ri-dashboard-2-line"></i><span>Dashboard</span>
        </a>
    </li>

    <li class="menu-title"><span>Administration</span></li>

    @canany(array_keys($userRoleMenu))
    <li class="nav-item">
        <a class="nav-link menu-link {{ $userRoleActive ? '' : 'collapsed' }}" href="#sidebarUserRoleR"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $userRoleActive ? 'true' : 'false' }}"
           aria-controls="sidebarUserRoleR">
            <i class="ri-settings-3-line"></i><span>User & Role Management</span>
        </a>
        <div class="collapse menu-dropdown {{ $userRoleActive ? 'show' : '' }}" id="sidebarUserRoleR">
            <ul class="nav nav-sm flex-column">
                @foreach($userRoleMenu as $permission => $item)
                @can($permission)
                <li class="nav-item">
                    <a href="{{ route($item['route']) }}"
                       class="nav-link {{ request()->routeIs($item['pattern']) ? 'active' : '' }}">
                        <i class="{{ $item['icon'] }}"></i> {{ Str::title($permission) }}
                    </a>
                </li>
                @endcan
                @endforeach
            </ul>
        </div>
    </li>
    @endcanany

    @can('view audit log')
    <li class="nav-item">
        <a class="nav-link menu-link {{ $auditLogActive ? 'active' : '' }}"
           href="{{ route('admin.audit-log.index') }}">
            <i class="ri-history-line"></i><span>{{ Str::title('view audit log') }}</span>
        </a>
    </li>
    @endcan

    @can('manage master')
    <li class="nav-item">
        <a class="nav-link menu-link {{ $masterActive ? '' : 'collapsed' }}" href="#sidebarMasterR"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $masterActive ? 'true' : 'false' }}"
           aria-controls="sidebarMasterR">
            <i class="ri-database-2-line"></i><span>{{ Str::title('manage master') }}</span>
        </a>
        <div class="collapse menu-dropdown {{ $masterActive ? 'show' : '' }}" id="sidebarMasterR">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.master.clients.index') }}"
                       class="nav-link {{ request()->routeIs('admin.master.clients.*') ? 'active' : '' }}">
                        <i class="ri-building-2-line"></i> Clients
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.master.sites.index') }}"
                       class="nav-link {{ request()->routeIs('admin.master.sites.*') ? 'active' : '' }}">
                        <i class="ri-map-pin-line"></i> Sites
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.master.buildings.index') }}"
                       class="nav-link {{ request()->routeIs('admin.master.buildings.*') ? 'active' : '' }}">
                        <i class="ri-home-office-line"></i> Buildings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.master.lookups.index') }}"
                       class="nav-link {{ request()->routeIs('admin.master.lookups.*') ? 'active' : '' }}">
                        <i class="ri-list-check-2"></i> Reference Data
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.master.sections.index') }}"
                       class="nav-link {{ request()->routeIs('admin.master.sections.*') ? 'active' : '' }}">
                        <i class="ri-layout-2-line"></i> Sections
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.master.data-types.index') }}"
                       class="nav-link {{ request()->routeIs('admin.master.data-types.*') ? 'active' : '' }}">
                        <i class="ri-list-settings-line"></i> Data Types
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.master.hierarchy.index') }}"
                       class="nav-link {{ request()->routeIs('admin.master.hierarchy.*') ? 'active' : '' }}">
                        <i class="ri-git-branch-line"></i> Hierarchy
                    </a>
                </li>
            </ul>
        </div>
    </li>
    @endcan

    <li class="menu-title"><span>Operations</span></li>

    @can('view jobs')
    <li class="nav-item">
        <a class="nav-link menu-link {{ $jobsActive ? '' : 'collapsed' }}" href="#sidebarJobsR"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $jobsActive ? 'true' : 'false' }}"
           aria-controls="sidebarJobsR">
            <i class="ri-briefcase-line"></i><span>{{ Str::title('view jobs') }}</span>
        </a>
        <div class="collapse menu-dropdown {{ $jobsActive ? 'show' : '' }}" id="sidebarJobsR">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.jobs.index') }}"
                       class="nav-link {{ request()->routeIs('admin.jobs.index') ? 'active' : '' }}">
                        <i class="ri-list-unordered"></i> All Jobs
                    </a>
                </li>
                @can('manage jobs')
                <li class="nav-item">
                    <a href="{{ route('admin.jobs.create') }}"
                       class="nav-link {{ request()->routeIs('admin.jobs.create') ? 'active' : '' }}">
                        <i class="ri-calendar-schedule-line"></i> Schedule Job
                    </a>
                </li>
                @endcan
            </ul>
        </div>
    </li>
    @endcan

    @canany(['review inspections', 'review reinspections', 'review installations'])
    @php $inspectionsRActive = request()->routeIs('reviewer.inspections.*'); @endphp
    <li class="nav-item">
        <a class="nav-link menu-link {{ $inspectionsRActive ? '' : 'collapsed' }}" href="#sidebarInspectionsR"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $inspectionsRActive ? 'true' : 'false' }}"
           aria-controls="sidebarInspectionsR">
            <i class="ri-survey-line"></i><span>Inspections</span>
        </a>
        <div class="collapse menu-dropdown {{ $inspectionsRActive ? 'show' : '' }}" id="sidebarInspectionsR">
            <ul class="nav nav-sm flex-column">
                @can('review inspections')
                <li class="nav-item">
                    <a href="{{ route('reviewer.inspections.index') }}"
                       class="nav-link {{ request()->routeIs('reviewer.inspections.*') && request('status', 'submitted') === 'submitted' ? 'active' : '' }}">
                        <i class="ri-time-line me-1"></i>Pending Review
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reviewer.inspections.index', ['status' => 'approved']) }}"
                       class="nav-link {{ request()->routeIs('reviewer.inspections.index') && request('status') === 'approved' ? 'active' : '' }}">
                        <i class="ri-checkbox-circle-line me-1"></i>Approved
                    </a>
                </li>
                @endcan
            </ul>
        </div>
    </li>
    @endcanany

    @can('view assets')
    <li class="nav-item">
        <a class="nav-link menu-link {{ $assetsActive ? '' : 'collapsed' }}" href="#sidebarAssetsR"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $assetsActive ? 'true' : 'false' }}"
           aria-controls="sidebarAssetsR">
            <i class="ri-tools-line"></i><span>{{ Str::title('view assets') }}</span>
        </a>
        <div class="collapse menu-dropdown {{ $assetsActive ? 'show' : '' }}" id="sidebarAssetsR">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.assets.index') }}"
                       class="nav-link {{ request()->routeIs('admin.assets.index') ? 'active' : '' }}">
                        <i class="ri-list-check-3"></i> All Assets
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.assets.index', ['status' => 'fail']) }}"
                       class="nav-link {{ request()->routeIs('admin.assets.*') && request('status') === 'fail' ? 'active' : '' }}">
                        <i class="ri-history-line"></i> Asset History
                    </a>
                </li>
            </ul>
        </div>
    </li>
    @endcan

    @endrole

    {{-- ===================== FIELD TECHNICIAN ===================== --}}
    @role('field-technician')

    <li class="menu-title"><span>Main</span></li>

    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('technician.dashboard') ? 'active' : '' }}"
           href="{{ route('technician.dashboard') }}">
            <i class="ri-dashboard-2-line"></i><span>Dashboard</span>
        </a>
    </li>

    <li class="menu-title"><span>My Work</span></li>

    @can('view jobs')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('technician.jobs.*') ? 'active' : '' }}"
           href="{{ route('technician.jobs.index') }}">
            <i class="ri-briefcase-line"></i><span>{{ Str::title('view jobs') }}</span>
        </a>
    </li>
    @endcan

    @canany(['capture inspections', 'capture reinspections', 'capture installations'])
    @php
        // Resolve the active work type from query param OR the current job in scope
        $activeWorkType = request('work_type')
            ?? (isset($job) ? $job->work_type : null);

        $captureActive = request()->routeIs('technician.jobs.inspect*', 'technician.jobs.install*', 'technician.jobs.show')
            || (request()->routeIs('technician.jobs.index') && in_array($activeWorkType, ['first_inspection', 're_inspection', 'installation', 'rectification', 'combined']));

        $isFirstInspection = $activeWorkType === 'first_inspection'
            && (request()->routeIs('technician.jobs.inspect*', 'technician.jobs.show')
                || (request()->routeIs('technician.jobs.index') && $activeWorkType === 'first_inspection'));

        $isReInspection = $activeWorkType === 're_inspection'
            && (request()->routeIs('technician.jobs.inspect*', 'technician.jobs.show')
                || (request()->routeIs('technician.jobs.index') && $activeWorkType === 're_inspection'));

        $isInstallation = in_array($activeWorkType, ['installation', 'rectification', 'combined'])
            && (request()->routeIs('technician.jobs.install*', 'technician.jobs.show')
                || (request()->routeIs('technician.jobs.index') && in_array($activeWorkType, ['installation', 'rectification', 'combined'])));
    @endphp
    <li class="nav-item">
        <a class="nav-link menu-link {{ $captureActive ? '' : 'collapsed' }}"
           href="#sidebarCapture" data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $captureActive ? 'true' : 'false' }}"
           aria-controls="sidebarCapture">
            <i class="ri-survey-line"></i><span>Capture</span>
        </a>
        <div class="collapse menu-dropdown {{ $captureActive ? 'show' : '' }}" id="sidebarCapture">
            <ul class="nav nav-sm flex-column">
                @can('capture inspections')
                <li class="nav-item">
                    <a href="{{ route('technician.jobs.index', ['work_type' => 'first_inspection', 'status' => 'in_progress']) }}"
                       class="nav-link {{ $isFirstInspection ? 'active' : '' }}">{{ Str::title('capture inspections') }}</a>
                </li>
                @endcan
                @can('capture reinspections')
                <li class="nav-item">
                    <a href="{{ route('technician.jobs.index', ['work_type' => 're_inspection', 'status' => 'in_progress']) }}"
                       class="nav-link {{ $isReInspection ? 'active' : '' }}">{{ Str::title('capture reinspections') }}</a>
                </li>
                @endcan
                @can('capture installations')
                <li class="nav-item">
                    <a href="{{ route('technician.jobs.index', ['work_type' => 'installation', 'status' => 'in_progress']) }}"
                       class="nav-link {{ $isInstallation ? 'active' : '' }}">{{ Str::title('capture installations') }}</a>
                </li>
                @endcan
            </ul>
        </div>
    </li>
    @endcanany

    @can('view assets')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('admin.assets.*') ? 'active' : '' }}"
           href="{{ route('admin.assets.index') }}">
            <i class="ri-tools-line"></i><span>{{ Str::title('view assets') }}</span>
        </a>
    </li>
    @endcan

    @endrole

    {{-- ===================== CLIENT USER ===================== --}}
    @role('client-user')

    <li class="menu-title"><span>Client Portal</span></li>

    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}"
           href="{{ route('client.dashboard') }}">
            <i class="ri-dashboard-2-line"></i><span>Overview</span>
        </a>
    </li>

    @endrole

    {{-- ===================== SHARED BOTTOM ===================== --}}
    <li class="menu-title"><span>Account</span></li>

    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
           href="{{ route('profile.edit') }}">
            <i class="ri-user-line"></i><span>Profile</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link menu-link" href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
            <i class="ri-logout-box-line"></i><span>Logout</span>
        </a>
        <form id="sidebar-logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
            @csrf
        </form>
    </li>

</ul>
