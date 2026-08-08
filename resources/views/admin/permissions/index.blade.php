<x-app-layout>
    <x-slot name="title">Permissions</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Permissions</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-key-2-line me-2 text-primary"></i>All Permissions
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $permissionGroups->flatten()->count() }}</span>
                    </h5>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addPermissionModal">
                        <i class="ri-add-line me-1"></i> Add Permission
                    </button>
                </div>
                <div class="card-body p-0">
                    @forelse($permissionGroups as $module => $permissions)
                    <div class="border-bottom">
                        <div class="px-3 py-2 bg-light">
                            <span class="fw-semibold text-muted fs-12 text-uppercase">
                                <i class="ri-apps-line me-1"></i>{{ $module }}
                                <span class="badge bg-secondary ms-1">{{ $permissions->count() }}</span>
                            </span>
                        </div>
                        <div class="px-3 py-2 d-flex flex-wrap gap-2">
                            @foreach($permissions as $permission)
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-secondary-subtle text-secondary fs-12 fw-normal px-2 py-1">
                                    {{ ucfirst($permission->name) }}
                                </span>
                                <button type="button"
                                        class="btn btn-xs btn-link text-danger p-0"
                                        title="Delete permission"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deletePermissionModal"
                                        data-permission-name="{{ $permission->name }}"
                                        data-action="{{ route('admin.permissions.destroy', $permission) }}">
                                    <i class="ri-close-line fs-14"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-5">
                        <i class="ri-key-2-line fs-24 d-block mb-2"></i>
                        No permissions defined yet.
                    </div>
                    @endforelse
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
    function toggleNewModule(select) {
        document.getElementById('newModuleField').style.display =
            select.value === '__new__' ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('deletePermissionModal').addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('deletePermissionName').textContent = btn.dataset.permissionName;
            document.getElementById('deletePermissionForm').action = btn.dataset.action;
        });

        @if($errors->has('name') || $errors->has('module') || $errors->has('new_module'))
        new bootstrap.Modal(document.getElementById('addPermissionModal')).show();
        toggleNewModule(document.getElementById('moduleSelect'));
        @endif
    });
    </script>
    @endpush

</x-app-layout>
