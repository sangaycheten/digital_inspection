<x-app-layout>
    <x-slot name="title">Permissions</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Permissions</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">User & Role Management</li>
                        <li class="breadcrumb-item active">Permissions</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible alert-border-left fade show" role="alert">
        <i class="ri-checkbox-circle-line me-3 align-middle fs-16"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible alert-border-left fade show" role="alert">
        <i class="ri-error-warning-line me-3 align-middle fs-16"></i>
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="row g-3 mb-3">
        <div class="col-sm-4">
            <div class="card card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded bg-primary-subtle text-primary fs-20">
                            <i class="ri-key-2-line"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Total Permissions</p>
                        <h4 class="fw-semibold mb-0">{{ $permissionGroups->flatten()->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded bg-success-subtle text-success fs-20">
                            <i class="ri-apps-line"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Module Groups</p>
                        <h4 class="fw-semibold mb-0">{{ $permissionGroups->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded bg-warning-subtle text-warning fs-20">
                            <i class="ri-shield-keyhole-line"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted fs-13 mb-0">Roles</p>
                        <h4 class="fw-semibold mb-0">{{ $roles->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-key-2-line me-2 text-primary"></i>All Permissions
                    </h5>
                    <!-- Module filter -->
                    <select id="moduleFilter" class="form-select form-select-sm" style="max-width:200px;" onchange="filterModule(this.value)">
                        <option value="">All Modules</option>
                        @foreach($permissionGroups->keys() as $mod)
                            <option value="{{ $mod }}">{{ $moduleLabels[$mod] ?? $mod }}</option>
                        @endforeach
                    </select>
                    @can('add permissions')
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addPermissionModal">
                        <i class="ri-add-line me-1"></i> Add Permission
                    </button>
                    @endcan
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="permissionsTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width:34px;">#</th>
                                    <th>Permission</th>
                                    <th style="min-width:160px;">Module / Group</th>
                                    <th>Assigned Roles</th>
                                    <th class="text-center" style="width:140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rowNum = 0; @endphp
                                @forelse($permissionGroups as $module => $permissions)
                                <tr class="module-divider" data-module="{{ $module }}">
                                    <td colspan="5" class="ps-3 py-2 bg-light">
                                        <span class="fw-semibold text-muted fs-12 text-uppercase">
                                            <i class="ri-apps-line me-1"></i>{{ $moduleLabels[$module] ?? $module }}
                                            <span class="badge bg-secondary ms-1">{{ $permissions->count() }}</span>
                                        </span>
                                    </td>
                                </tr>
                                @foreach($permissions as $permission)
                                @php $rowNum++; @endphp
                                <tr data-module="{{ $module }}">
                                    <td class="ps-3 text-muted fs-13">{{ $rowNum }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-xs flex-shrink-0">
                                                <span class="avatar-title rounded bg-primary-subtle text-primary fs-14">
                                                    <i class="ri-key-2-line"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <span class="fw-medium">{{ Str::title($permission->name) }}</span>
                                                <div class="text-muted fs-12">{{ $permission->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $moduleLabels[$module] ?? $module }}</span>
                                    </td>
                                    <td>
                                        @php $assignedRoles = $permission->roles; @endphp
                                        @if($assignedRoles->isEmpty())
                                            <span class="text-muted fs-13"><i class="ri-close-circle-line me-1"></i>None</span>
                                        @else
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($assignedRoles as $role)
                                                <span class="badge
                                                    @if($role->name === 'system-administrator') bg-danger
                                                    @elseif($role->name === 'manager') bg-warning text-dark
                                                    @elseif($role->name === 'field-technician') bg-primary
                                                    @else bg-success
                                                    @endif fs-11">
                                                    {{ role_label($role->name) }}
                                                </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- View -->
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="View details"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewPermissionModal"
                                                    data-permission-name="{{ $permission->name }}"
                                                    data-permission-module="{{ $moduleLabels[$module] ?? $module }}"
                                                    data-permission-roles="{{ $assignedRoles->pluck('name')->join(',') }}">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                            <!-- Edit -->
                                            @can('edit permissions')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Edit permission"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editPermissionModal"
                                                    data-permission-id="{{ $permission->id }}"
                                                    data-permission-name="{{ $permission->name }}"
                                                    data-permission-module="{{ $permission->module }}"
                                                    data-action="{{ route('admin.permissions.update', $permission) }}">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                            @endcan
                                            <!-- Delete -->
                                            @can('delete permissions')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete permission"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deletePermissionModal"
                                                    data-permission-name="{{ $permission->name }}"
                                                    data-action="{{ route('admin.permissions.destroy', $permission) }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="ri-key-2-line fs-24 d-block mb-2"></i>
                                        No permissions defined yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Permission Modal -->
    <div class="modal fade" id="viewPermissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-key-2-line me-2 text-primary"></i>Permission Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-muted fs-12 text-uppercase">Permission Name</label>
                        <p id="viewPermName" class="fw-semibold fs-15 mb-0"></p>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium text-muted fs-12 text-uppercase">Module / Group</label>
                        <p id="viewPermModule" class="mb-0"></p>
                    </div>
                    <div>
                        <label class="form-label fw-medium text-muted fs-12 text-uppercase mb-2">Role Assignments</label>
                        <div id="viewPermRoles" class="d-flex flex-column gap-2">
                            @foreach($roles as $role)
                            <div class="d-flex align-items-center justify-content-between p-2 border rounded"
                                 data-role-name="{{ $role->name }}">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar-xs">
                                        <span class="avatar-title rounded-circle fs-13
                                            @if($role->name === 'system-administrator') bg-danger-subtle text-danger
                                            @elseif($role->name === 'manager') bg-warning-subtle text-warning
                                            @elseif($role->name === 'field-technician') bg-primary-subtle text-primary
                                            @else bg-success-subtle text-success
                                            @endif">
                                            <i class="ri-shield-keyhole-line"></i>
                                        </span>
                                    </span>
                                    <span class="fw-medium fs-14">{{ role_label($role->name) }}</span>
                                </div>
                                <span class="role-status-badge"></span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Permission Modal -->
    <div class="modal fade" id="deletePermissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="avatar-sm mx-auto mb-3">
                        <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-22">
                            <i class="ri-delete-bin-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-3">Delete Permission</h5>
                    <p class="text-muted mb-4">
                        Are you sure you want to delete <strong id="deletePermissionName"></strong>?
                        This will remove it from all roles.
                    </p>
                    <div class="hstack gap-2 justify-content-center">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <form id="deletePermissionForm" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Permission Modal -->
    <div class="modal fade" id="editPermissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-pencil-line me-2"></i>Edit Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editPermissionForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Permission Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editPermissionName"
                                   class="form-control" placeholder="e.g. view reports" required>
                            <div class="form-text text-muted">Saved in lowercase. Navigation references update automatically.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Module / Group <span class="text-danger">*</span></label>
                            <select name="module" id="editModuleSelect" class="form-select"
                                    onchange="toggleEditNewModule(this)" required>
                                <option value="">-- Select Module --</option>
                                @foreach($modules as $mod)
                                    <option value="{{ $mod }}">{{ $mod }}</option>
                                @endforeach
                                <option value="__new__">+ Create new module</option>
                            </select>
                        </div>
                        <div class="mb-3" id="editNewModuleField" style="display:none;">
                            <label class="form-label">New Module Name <span class="text-danger">*</span></label>
                            <input type="text" name="new_module" id="editNewModuleName"
                                   class="form-control" placeholder="e.g. Reports">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Permission Modal -->
    <div class="modal fade" id="addPermissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-key-2-line me-2"></i>Add New Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.permissions.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Permission Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   placeholder="e.g. view reports"
                                   value="{{ old('name') }}" required>
                            <div class="form-text text-muted">Use lowercase words separated by spaces.</div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Module / Group <span class="text-danger">*</span></label>
                            <select name="module" id="moduleSelect" class="form-select @error('module') is-invalid @enderror"
                                    onchange="toggleNewModule(this)" required>
                                <option value="">-- Select Module --</option>
                                @foreach($modules as $mod)
                                    <option value="{{ $mod }}" {{ old('module') === $mod ? 'selected' : '' }}>
                                        {{ $mod }}
                                    </option>
                                @endforeach
                                <option value="__new__" {{ old('module') === '__new__' ? 'selected' : '' }}>+ Create new module</option>
                            </select>
                            @error('module')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3" id="newModuleField" style="display:none;">
                            <label class="form-label">New Module Name <span class="text-danger">*</span></label>
                            <input type="text" name="new_module" class="form-control"
                                   placeholder="e.g. Reports"
                                   value="{{ old('new_module') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ri-add-line me-1"></i> Create Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function filterModule(val) {
        document.querySelectorAll('#permissionsTable tbody tr').forEach(function (tr) {
            if (!val) { tr.style.display = ''; return; }
            tr.style.display = (tr.dataset.module === val) ? '' : 'none';
        });
    }

    function toggleNewModule(select) {
        document.getElementById('newModuleField').style.display =
            select.value === '__new__' ? 'block' : 'none';
    }

    function toggleEditNewModule(select) {
        document.getElementById('editNewModuleField').style.display =
            select.value === '__new__' ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {

        // View modal
        document.getElementById('viewPermissionModal').addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            var name    = btn.dataset.permissionName;
            var module  = btn.dataset.permissionModule;
            var roles   = btn.dataset.permissionRoles ? btn.dataset.permissionRoles.split(',') : [];

            document.getElementById('viewPermName').textContent   = name.replace(/\b\w/g, c => c.toUpperCase());
            document.getElementById('viewPermModule').textContent  = module;

            document.querySelectorAll('#viewPermRoles [data-role-name]').forEach(function (row) {
                var roleName = row.dataset.roleName;
                var badge    = row.querySelector('.role-status-badge');
                var assigned = roles.includes(roleName);
                badge.className = 'role-status-badge badge ' + (assigned
                    ? 'bg-success-subtle text-success'
                    : 'bg-secondary-subtle text-secondary');
                badge.innerHTML = assigned
                    ? '<i class="ri-checkbox-circle-fill me-1"></i>Assigned'
                    : '<i class="ri-close-circle-line me-1"></i>Not assigned';
            });
        });

        // Delete modal
        document.getElementById('deletePermissionModal').addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('deletePermissionName').textContent = btn.dataset.permissionName;
            document.getElementById('deletePermissionForm').action = btn.dataset.action;
        });

        // Edit modal
        document.getElementById('editPermissionModal').addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('editPermissionName').value  = btn.dataset.permissionName;
            document.getElementById('editPermissionForm').action = btn.dataset.action;

            var moduleSelect  = document.getElementById('editModuleSelect');
            var currentModule = btn.dataset.permissionModule;
            var opt = Array.from(moduleSelect.options).find(o => o.value === currentModule);
            if (opt) {
                moduleSelect.value = currentModule;
            } else {
                moduleSelect.value = '__new__';
                document.getElementById('editNewModuleName').value = currentModule;
            }
            toggleEditNewModule(moduleSelect);
        });

        @if($errors->has('name') || $errors->has('module') || $errors->has('new_module'))
        new bootstrap.Modal(document.getElementById('addPermissionModal')).show();
        toggleNewModule(document.getElementById('moduleSelect'));
        @endif
    });
    </script>
    @endpush

</x-app-layout>
