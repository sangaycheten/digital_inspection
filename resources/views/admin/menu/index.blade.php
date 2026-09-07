<x-app-layout>
    <x-slot name="title">Menu Elements</x-slot>

    @push('styles')
    <style>
        .menu-item-card { border: 1px solid var(--vz-border-color); border-radius: 8px; margin-bottom: 6px; overflow: hidden; }
        .menu-item-header { display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: var(--vz-secondary-bg); }
        .menu-item-header:hover { background: var(--vz-tertiary-bg); }
        .menu-item-header .seq-badge { min-width: 26px; text-align: center; }
        .menu-children { border-top: 1px solid var(--vz-border-color); }
        .menu-child-row { display: flex; align-items: center; gap: 10px; padding: 8px 14px 8px 44px; border-bottom: 1px solid var(--vz-border-color); background: var(--vz-body-bg); }
        .menu-child-row:last-child { border-bottom: none; }
        .menu-child-row:hover { background: var(--vz-tertiary-bg); }
        .menu-btn-group { display: flex; gap: 4px; margin-left: auto; flex-shrink: 0; }
        .toggle-icon { transition: transform .2s; }
    </style>
    @endpush

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Menu Elements</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">User & Role Management</li>
                        <li class="breadcrumb-item active">Menu Elements</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-border-left alert-dismissible fade show">
        <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-border-left alert-dismissible fade show">
        <i class="ri-error-warning-line me-2"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-menu-2-line me-2 text-primary"></i>
                        Sidebar Menu
                        <span class="badge bg-primary-subtle text-primary ms-1">System Administrator</span>
                    </h5>
                </div>
                <div class="card-body p-3">

                    @foreach($topLevel as $item)
                    @php $subs = $children->get($item->key, collect()); @endphp

                    <div class="menu-item-card">

                        {{-- Parent row --}}
                        <div class="menu-item-header">

                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="badge bg-secondary-subtle text-secondary seq-badge fs-12">{{ $item->sequence }}</span>
                                <i class="{{ $item->icon }} text-primary fs-16"></i>
                                <span class="fw-semibold fs-14">{{ $item->label }}</span>
                                @if($subs->isNotEmpty())
                                <span class="badge bg-light text-muted border fs-11">
                                    <i class="ri-list-unordered me-1"></i>{{ $subs->count() }} sub-items
                                </span>
                                @endif
                            </div>
                            @if($subs->isNotEmpty())
                            <button type="button" class="btn btn-sm btn-link text-muted p-1 me-1" title="Collapse/expand sub-items"
                                    onclick="toggleChildren('sub_{{ $item->key }}', '{{ $item->key }}')">
                                <i class="ri-arrow-down-s-line fs-18 toggle-icon" id="icon_{{ $item->key }}"></i>
                            </button>
                            @endif

                            <div class="menu-btn-group">
                                <!-- Edit -->
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        title="Edit label"
                                        data-bs-toggle="modal" data-bs-target="#editMenuModal"
                                        data-item-id="{{ $item->id }}"
                                        data-item-label="{{ $item->label }}"
                                        data-item-icon="{{ $item->icon }}"
                                        data-action="{{ route('admin.menu.update', $item) }}">
                                    <i class="ri-pencil-line"></i>
                                </button>
                                <!-- Move up -->
                                <form method="POST" action="{{ route('admin.menu.moveUp', $item) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary {{ $loop->first ? 'disabled' : '' }}"
                                            @if($loop->first) tabindex="-1" @endif title="Move up">
                                        <i class="ri-arrow-up-line"></i>
                                    </button>
                                </form>
                                <!-- Move down -->
                                <form method="POST" action="{{ route('admin.menu.moveDown', $item) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary {{ $loop->last ? 'disabled' : '' }}"
                                            @if($loop->last) tabindex="-1" @endif title="Move down">
                                        <i class="ri-arrow-down-line"></i>
                                    </button>
                                </form>
                                <!-- Delete -->
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        title="Delete"
                                        data-bs-toggle="modal" data-bs-target="#deleteMenuModal"
                                        data-item-label="{{ $item->label }}"
                                        data-item-note="{{ $subs->isNotEmpty() ? 'This will also delete its ' . $subs->count() . ' sub-item(s).' : '' }}"
                                        data-action="{{ route('admin.menu.destroy', $item) }}">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Sub-menu rows --}}
                        @if($subs->isNotEmpty())
                        <div class="menu-children show" id="sub_{{ $item->key }}">
                            @foreach($subs as $child)
                            <div class="menu-child-row">
                                <span class="badge bg-light text-muted border seq-badge fs-11">{{ $child->sequence }}</span>
                                <i class="{{ $child->icon }} text-secondary fs-14"></i>
                                <span class="fs-13 flex-grow-1">{{ $child->label }}</span>
                                <div class="menu-btn-group">
                                    <!-- Edit child -->
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            title="Edit label"
                                            data-bs-toggle="modal" data-bs-target="#editMenuModal"
                                            data-item-id="{{ $child->id }}"
                                            data-item-label="{{ $child->label }}"
                                            data-item-icon="{{ $child->icon }}"
                                            data-action="{{ route('admin.menu.update', $child) }}">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                    <!-- Move up -->
                                    <form method="POST" action="{{ route('admin.menu.moveUp', $child) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary {{ $loop->first ? 'disabled' : '' }}"
                                                @if($loop->first) tabindex="-1" @endif title="Move up">
                                            <i class="ri-arrow-up-line"></i>
                                        </button>
                                    </form>
                                    <!-- Move down -->
                                    <form method="POST" action="{{ route('admin.menu.moveDown', $child) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary {{ $loop->last ? 'disabled' : '' }}"
                                                @if($loop->last) tabindex="-1" @endif title="Move down">
                                            <i class="ri-arrow-down-line"></i>
                                        </button>
                                    </form>
                                    <!-- Delete child -->
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            data-bs-toggle="modal" data-bs-target="#deleteMenuModal"
                                            data-item-label="{{ $child->label }}"
                                            data-item-note=""
                                            data-action="{{ route('admin.menu.destroy', $child) }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                    </div>
                    @endforeach

                </div>
                <div class="card-footer text-muted fs-12">
                    <i class="ri-information-line me-1"></i>
                    Click a group header to expand/collapse. Use the pencil to rename items, arrows to reorder.
                </div>
            </div>
        </div>

        <!-- Help panel -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-information-line me-2 text-info"></i>About Menu Elements</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted fs-13 mb-3">
                        This page controls the sidebar navigation for the <strong>System Administrator</strong> role.
                        Other roles use static menus defined in the system configuration.
                    </p>
                    <ul class="list-unstyled fs-13 text-muted mb-0">
                        <li class="mb-2"><i class="ri-pencil-line text-primary me-2"></i>Edit — rename the label or change the icon</li>
                        <li class="mb-2"><i class="ri-arrow-up-line text-secondary me-2"></i>Arrows — reorder items within their level</li>
                        <li class="mb-2"><i class="ri-delete-bin-line text-danger me-2"></i>Delete — removes the item from the sidebar</li>
                    </ul>
                    <div class="alert alert-warning alert-border-left mt-3 p-2 fs-12 mb-0">
                        <i class="ri-alert-line me-1"></i>
                        Deleting a group also removes all its sub-items. Changes take effect immediately.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editMenuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-pencil-line me-2"></i>Edit Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editMenuForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Display Label <span class="text-danger">*</span></label>
                            <input type="text" name="label" id="editMenuLabel"
                                   class="form-control" placeholder="e.g. User & Role Management" required>
                            <div class="form-text text-muted">This is the name shown in the sidebar.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon <span class="text-muted fs-12">(Remix Icon class)</span></label>
                            <div class="input-group">
                                <span class="input-group-text" id="iconPreview"><i id="iconPreviewIcon" class="ri-circle-line fs-16"></i></span>
                                <input type="text" name="icon" id="editMenuIcon"
                                       class="form-control" placeholder="e.g. ri-shield-user-line"
                                       oninput="previewIcon(this.value)">
                            </div>
                            <div class="form-text text-muted">Browse icons at <a href="https://remixicon.com" target="_blank">remixicon.com</a></div>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteMenuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="avatar-sm mx-auto mb-3">
                        <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-22">
                            <i class="ri-delete-bin-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-2">Delete Menu Item</h5>
                    <p class="text-muted mb-1">
                        Remove <strong id="deleteMenuLabel"></strong> from the sidebar?
                    </p>
                    <p class="text-danger fs-13 mb-4" id="deleteMenuNote"></p>
                    <div class="hstack gap-2 justify-content-center">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <form id="deleteMenuForm" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function toggleChildren(id, key) {
        const el   = document.getElementById(id);
        const icon = document.getElementById('icon_' + key);
        if (!el) return;
        el.classList.toggle('show');
        if (icon) icon.style.transform = el.classList.contains('show') ? '' : 'rotate(-90deg)';
    }

    function previewIcon(cls) {
        const el = document.getElementById('iconPreviewIcon');
        el.className = cls.trim() || 'ri-circle-line';
        el.className += ' fs-16';
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('editMenuModal').addEventListener('show.bs.modal', function (e) {
            var btn   = e.relatedTarget;
            var label = btn.dataset.itemLabel;
            var icon  = btn.dataset.itemIcon;
            document.getElementById('editMenuLabel').value  = label;
            document.getElementById('editMenuIcon').value   = icon;
            document.getElementById('editMenuForm').action  = btn.dataset.action;
            previewIcon(icon);
        });

        document.getElementById('deleteMenuModal').addEventListener('show.bs.modal', function (e) {
            var btn  = e.relatedTarget;
            document.getElementById('deleteMenuLabel').textContent = btn.dataset.itemLabel;
            document.getElementById('deleteMenuNote').textContent  = btn.dataset.itemNote;
            document.getElementById('deleteMenuForm').action = btn.dataset.action;
        });
    });
    </script>
    @endpush

</x-app-layout>
