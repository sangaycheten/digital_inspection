<x-app-layout>
    @php $title = 'RBAC / Roles'; @endphp

    @push('styles')
    <style>
        .permission-matrix th { white-space: nowrap; }
        .permission-matrix td.module-header {
            font-weight: 600;
            background-color: var(--vz-secondary-bg);
            color: var(--vz-heading-color);
        }
        .permission-check {
            text-align: center;
            vertical-align: middle;
        }
        .permission-check .form-check { margin: 0; justify-content: center; display: flex; }
        .role-col-header {
            text-align: center;
            min-width: 130px;
        }
        .badge-role { font-size: 0.7rem; }
        .permission-check [title] { cursor: help; }
    </style>
    @endpush

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">RBAC / Roles & Permissions</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">RBAC / Roles</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible alert-border-left fade show" role="alert">
        <i class="ri-checkbox-circle-line me-3 align-middle fs-16"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible alert-border-left fade show" role="alert">
        <i class="ri-error-warning-line me-3 align-middle fs-16"></i>
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Role Summary Cards -->
    <div class="row">
        @foreach($roles as $role)
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title rounded fs-3
                                @if($role->name === 'system-administrator') bg-danger-subtle
                                @elseif($role->name === 'manager') bg-warning-subtle
                                @elseif($role->name === 'field-technician') bg-primary-subtle
                                @else bg-success-subtle
                                @endif">
                                <i class="ri-shield-keyhole-line
                                    @if($role->name === 'system-administrator') text-danger
                                    @elseif($role->name === 'manager') text-warning
                                    @elseif($role->name === 'field-technician') text-primary
                                    @else text-success
                                    @endif"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1 fs-12">
                                {{ $role->users->count() }} User(s)
                            </p>
                            <h5 class="fw-semibold mb-0" style="font-size: 0.85rem;">
                                {{ role_label($role->name) }}
                            </h5>
                        </div>
                        <div class="flex-shrink-0 ms-2">
                            <span class="badge bg-light text-dark fs-12">
                                {{ $role->permissions->count() }} perms
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Permission Matrix -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h4 class="card-title mb-0 flex-grow-1">
                        <i class="ri-table-line me-2 text-primary"></i>Permission Matrix
                    </h4>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editPermissionsModal">
                        <i class="ri-edit-line me-1"></i> Edit Permissions
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered permission-matrix mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th style="min-width: 200px;">Permission</th>
                                    @foreach($roles as $role)
                                    <th class="role-col-header">
                                        <div>{{ role_label($role->name) }}</div>
                                        <span class="badge badge-role mt-1
                                            @if($role->name === 'system-administrator') bg-danger
                                            @elseif($role->name === 'manager') bg-warning text-dark
                                            @elseif($role->name === 'field-technician') bg-primary
                                            @else bg-success
                                            @endif">
                                            {{ $role->users->count() }} user(s)
                                        </span>
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissionGroups as $module => $permissions)
                                <tr>
                                    <td class="module-header" colspan="{{ $roles->count() + 1 }}">
                                        <i class="ri-apps-line me-1"></i> {{ $module }}
                                    </td>
                                </tr>
                                @foreach($permissions as $permission)
                                <tr>
                                    <td class="ps-4">
                                        <span class="text-muted">{{ ucfirst($permission->name) }}</span>
                                    </td>
                                    @foreach($roles as $role)
                                    <td class="permission-check">
                                        @if($module === 'Master')
                                            @if($role->name === 'system-administrator')
                                                <span class="text-success fs-18" title="Always granted — System Administrator only">
                                                    <i class="ri-lock-fill"></i>
                                                </span>
                                            @else
                                                <span class="text-muted fs-18" title="Restricted to System Administrator">
                                                    <i class="ri-lock-line"></i>
                                                </span>
                                            @endif
                                        @elseif($role->hasPermissionTo($permission->name))
                                            <span class="text-success fs-18"><i class="ri-checkbox-circle-fill"></i></span>
                                        @else
                                            <span class="text-muted fs-18"><i class="ri-close-circle-line"></i></span>
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users by Role -->
    <div class="row">
        @foreach($roles as $role)
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        {{ role_label($role->name) }}
                        <span class="badge bg-secondary ms-2">{{ $role->users->count() }}</span>
                    </h5>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ri-team-line"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($role->users->isEmpty())
                        <p class="text-muted text-center py-3 mb-0">No users assigned to this role.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Name</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($role->users as $user)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-xs flex-shrink-0">
                                                <span class="avatar-title rounded-circle bg-primary-subtle text-primary fs-12">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </span>
                                            </div>
                                            {{ $user->name }}
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ $user->email }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Edit Permissions Modal -->
    <div class="modal fade" id="editPermissionsModal" tabindex="-1" aria-labelledby="editPermissionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPermissionsModalLabel">
                        <i class="ri-shield-keyhole-line me-2"></i>Edit Role Permissions
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rbacPermissionsForm" action="{{ route('admin.rbac.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="alert alert-warning alert-border-left mb-3">
                            <i class="ri-alert-line me-2"></i>
                            Changes take effect immediately for all users with that role.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered permission-matrix">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="min-width: 200px;">Permission</th>
                                        @foreach($roles as $role)
                                        <th class="role-col-header">{{ role_label($role->name) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permissionGroups as $module => $permissions)
                                    <tr>
                                        <td class="module-header" colspan="{{ $roles->count() + 1 }}">
                                            <i class="ri-apps-line me-1"></i> {{ $module }}
                                        </td>
                                    </tr>
                                    @foreach($permissions as $permission)
                                    <tr>
                                        <td class="ps-4">{{ ucfirst($permission->name) }}</td>
                                        @foreach($roles as $role)
                                        <td class="permission-check">
                                            @if($module === 'Master')
                                                @if($role->name === 'system-administrator')
                                                    <span class="text-success fs-18" title="Always granted — System Administrator only">
                                                        <i class="ri-lock-fill"></i>
                                                    </span>
                                                @else
                                                    <span class="text-muted fs-18" title="Restricted to System Administrator">
                                                        <i class="ri-lock-line"></i>
                                                    </span>
                                                @endif
                                            @else
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="permissions[{{ $role->id }}][]"
                                                        value="{{ $permission->name }}"
                                                        id="perm_{{ $role->id }}_{{ $permission->id }}"
                                                        @checked($role->hasPermissionTo($permission->name))>
                                                </div>
                                            @endif
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="rbacPermissionsForm" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
