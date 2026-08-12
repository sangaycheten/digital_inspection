<ul class="navbar-nav" id="navbar-nav">

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

    @php
        $adminSettingsActive = request()->routeIs('admin.users.*')
            || request()->routeIs('admin.rbac.*')
            || request()->routeIs('admin.permissions.*')
            || request()->routeIs('admin.audit-log.*');
        $masterActive = request()->routeIs('admin.master.*');
        $questionnairesActive = request()->routeIs('admin.questionnaires.*');
        $assetsActive = request()->routeIs('admin.assets.*');
        $jobsActive   = request()->routeIs('admin.jobs.*');
    @endphp

    <li class="nav-item">
        <a class="nav-link menu-link {{ $adminSettingsActive ? '' : 'collapsed' }}" href="#sidebarSystemSettings"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $adminSettingsActive ? 'true' : 'false' }}"
           aria-controls="sidebarSystemSettings">
            <i class="ri-settings-3-line"></i><span>System Settings</span>
        </a>
        <div class="collapse menu-dropdown {{ $adminSettingsActive ? 'show' : '' }}" id="sidebarSystemSettings">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}"
                       class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="ri-team-line"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.rbac.index') }}"
                       class="nav-link {{ request()->routeIs('admin.rbac.*') ? 'active' : '' }}">
                        <i class="ri-shield-keyhole-line"></i> Roles
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.permissions.index') }}"
                       class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                        <i class="ri-key-2-line"></i> Permissions
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.audit-log.index') }}"
                       class="nav-link {{ request()->routeIs('admin.audit-log.*') ? 'active' : '' }}">
                        <i class="ri-history-line"></i> Audit Log
                    </a>
                </li>
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link menu-link {{ $masterActive ? '' : 'collapsed' }}" href="#sidebarMaster"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $masterActive ? 'true' : 'false' }}"
           aria-controls="sidebarMaster">
            <i class="ri-database-2-line"></i><span>Master</span>
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
                        <i class="ri-list-check-2"></i> Lookups
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
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link menu-link {{ $questionnairesActive ? 'active' : '' }}"
           href="{{ route('admin.questionnaires.index') }}">
            <i class="ri-questionnaire-line"></i><span>Questionnaires</span>
        </a>
    </li>

    <li class="menu-title"><span>Operations</span></li>

    <li class="nav-item">
        <a class="nav-link menu-link {{ $jobsActive ? '' : 'collapsed' }}" href="#sidebarJobs"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $jobsActive ? 'true' : 'false' }}"
           aria-controls="sidebarJobs">
            <i class="ri-briefcase-line"></i><span>Jobs</span>
        </a>
        <div class="collapse menu-dropdown {{ $jobsActive ? 'show' : '' }}" id="sidebarJobs">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.jobs.index') }}"
                       class="nav-link {{ request()->routeIs('admin.jobs.index') ? 'active' : '' }}">
                        <i class="ri-list-unordered"></i> All Jobs
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.jobs.create') }}"
                       class="nav-link {{ request()->routeIs('admin.jobs.create') ? 'active' : '' }}">
                        <i class="ri-calendar-schedule-line"></i> Schedule Job
                    </a>
                </li>
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link menu-link collapsed" href="#sidebarInspections" data-bs-toggle="collapse" role="button"
           aria-expanded="false" aria-controls="sidebarInspections">
            <i class="ri-survey-line"></i><span>Inspections</span>
        </a>
        <div class="collapse menu-dropdown" id="sidebarInspections">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item"><a href="#" class="nav-link">Review Inspections</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Re-Inspections</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Installations</a></li>
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link menu-link {{ $assetsActive ? '' : 'collapsed' }}" href="#sidebarAssets"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $assetsActive ? 'true' : 'false' }}"
           aria-controls="sidebarAssets">
            <i class="ri-tools-line"></i><span>Asset Register</span>
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

    <li class="nav-item">
        <a class="nav-link menu-link collapsed" href="#sidebarDocuments" data-bs-toggle="collapse" role="button"
           aria-expanded="false" aria-controls="sidebarDocuments">
            <i class="ri-file-list-3-line"></i><span>Documents</span>
        </a>
        <div class="collapse menu-dropdown" id="sidebarDocuments">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item"><a href="#" class="nav-link">All Documents</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Issued</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Superseded</a></li>
            </ul>
        </div>
    </li>

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

    <li class="menu-title"><span>Operations</span></li>

    <li class="nav-item">
        <a class="nav-link menu-link {{ $jobsActive ? '' : 'collapsed' }}" href="#sidebarJobsR"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $jobsActive ? 'true' : 'false' }}"
           aria-controls="sidebarJobsR">
            <i class="ri-briefcase-line"></i><span>Jobs</span>
        </a>
        <div class="collapse menu-dropdown {{ $jobsActive ? 'show' : '' }}" id="sidebarJobsR">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.jobs.index') }}"
                       class="nav-link {{ request()->routeIs('admin.jobs.index') ? 'active' : '' }}">
                        <i class="ri-list-unordered"></i> All Jobs
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.jobs.create') }}"
                       class="nav-link {{ request()->routeIs('admin.jobs.create') ? 'active' : '' }}">
                        <i class="ri-calendar-schedule-line"></i> Schedule Job
                    </a>
                </li>
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link menu-link collapsed" href="#sidebarInspectionsR" data-bs-toggle="collapse" role="button"
           aria-expanded="false" aria-controls="sidebarInspectionsR">
            <i class="ri-survey-line"></i><span>Inspections</span>
        </a>
        <div class="collapse menu-dropdown" id="sidebarInspectionsR">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item"><a href="#" class="nav-link">Review Inspections</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Re-Inspections</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Installations</a></li>
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link menu-link {{ $assetsActive ? '' : 'collapsed' }}" href="#sidebarAssetsR"
           data-bs-toggle="collapse" role="button"
           aria-expanded="{{ $assetsActive ? 'true' : 'false' }}"
           aria-controls="sidebarAssetsR">
            <i class="ri-tools-line"></i><span>Asset Register</span>
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

    <li class="nav-item">
        <a class="nav-link menu-link collapsed" href="#sidebarDocumentsR" data-bs-toggle="collapse" role="button"
           aria-expanded="false" aria-controls="sidebarDocumentsR">
            <i class="ri-file-list-3-line"></i><span>Documents</span>
        </a>
        <div class="collapse menu-dropdown" id="sidebarDocumentsR">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item"><a href="#" class="nav-link">Pending Approval</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Approved</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Issued</a></li>
            </ul>
        </div>
    </li>

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

    <li class="nav-item">
        <a class="nav-link menu-link" href="#">
            <i class="ri-briefcase-line"></i><span>My Jobs</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link menu-link collapsed" href="#sidebarCapture" data-bs-toggle="collapse" role="button"
           aria-expanded="false" aria-controls="sidebarCapture">
            <i class="ri-survey-line"></i><span>Capture</span>
        </a>
        <div class="collapse menu-dropdown" id="sidebarCapture">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item"><a href="#" class="nav-link">First Inspection</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Re-Inspection</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Installation / Rectification</a></li>
            </ul>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link menu-link" href="#">
            <i class="ri-tools-line"></i><span>Assets</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link menu-link" href="#">
            <i class="ri-file-list-3-line"></i><span>My Documents</span>
        </a>
    </li>

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

    <li class="nav-item">
        <a class="nav-link menu-link" href="#">
            <i class="ri-building-line"></i><span>My Sites</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link menu-link" href="#">
            <i class="ri-tools-line"></i><span>Assets</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link menu-link" href="#">
            <i class="ri-file-list-3-line"></i><span>Documents</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link menu-link" href="#">
            <i class="ri-download-2-line"></i><span>Export</span>
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
