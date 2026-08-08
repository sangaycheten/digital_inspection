<x-app-layout>
    <x-slot name="title">Data Types</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Data Types</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item active">Data Types</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible alert-border-left fade show" role="alert">
        <i class="ri-checkbox-circle-line me-3 align-middle fs-16"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-list-settings-line me-2 text-primary"></i>All Data Types
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $fieldTypes->total() }}</span>
                    </h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createDataTypeModal">
                        <i class="ri-add-line me-1"></i> Add Data Type
                    </button>
                </div>

                {{-- Filters --}}
                <div class="card-body border-bottom pb-3">
                    <form method="GET" action="{{ route('admin.master.data-types.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label text-muted fs-12 mb-1">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search by name..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted fs-12 mb-1">Type</label>
                            <select name="type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                @foreach($typeOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-search-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.master.data-types.index') }}" class="btn btn-light btn-sm ms-1">
                                <i class="ri-refresh-line"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width:50px;">#</th>
                                    <th>Name</th>
                                    <th style="width:130px;">Type</th>
                                    <th>Options</th>
                                    <th style="width:90px;">Status</th>
                                    <th style="width:110px;">Created</th>
                                    <th style="width:90px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fieldTypes as $ft)
                                <tr>
                                    <td class="ps-3 text-muted fs-12">{{ $fieldTypes->firstItem() + $loop->index }}</td>
                                    <td class="fw-medium">{{ $ft->name }}</td>
                                    <td>
                                        <span class="badge {{ $ft->typeBadgeClass() }}">{{ $ft->typeLabel() }}</span>
                                    </td>
                                    <td>
                                        @if($ft->hasOptions() && !empty($ft->options))
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($ft->options as $opt)
                                                    <span class="badge bg-light text-dark border">{{ $opt }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted fs-12">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ft->status === 'active')
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-muted fs-12">{{ $ft->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="hstack gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#editDataTypeModal"
                                                    data-id="{{ $ft->id }}"
                                                    data-name="{{ $ft->name }}"
                                                    data-type="{{ $ft->type }}"
                                                    data-options="{{ json_encode($ft->options ?? []) }}"
                                                    data-status="{{ $ft->status }}">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal" data-bs-target="#deleteDataTypeModal{{ $ft->id }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>

                                        {{-- Delete Modal --}}
                                        <div class="modal fade" id="deleteDataTypeModal{{ $ft->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="avatar-sm mx-auto mb-3">
                                                            <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-22">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </span>
                                                        </div>
                                                        <h5 class="mb-3">Delete Data Type</h5>
                                                        <p class="text-muted mb-4">Are you sure you want to delete <strong>{{ $ft->name }}</strong>?</p>
                                                        <div class="hstack gap-2 justify-content-center">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST" action="{{ route('admin.master.data-types.destroy', $ft) }}">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="ri-list-settings-line fs-24 d-block mb-2"></i>No data types found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($fieldTypes->hasPages())
                <div class="card-footer">{{ $fieldTypes->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===================== CREATE MODAL ===================== --}}
    <div class="modal fade" id="createDataTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-list-settings-line me-2"></i>Add New Data Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.master.data-types.store') }}" id="createDataTypeForm">
                    @csrf
                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="createName"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required maxlength="255"
                                   placeholder="e.g. Pass / Fail, Condition Rating">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Data Type <span class="text-danger">*</span></label>
                            <select name="type" id="createType"
                                    class="form-select @error('type') is-invalid @enderror"
                                    required onchange="onCreateTypeChange(this.value)">
                                <option value="">— Select type —</option>
                                @foreach($typeOptions as $value => $label)
                                <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Dynamic options section --}}
                        <div id="createOptionsWrap" style="display:none;">
                            <label class="form-label">Options <span class="text-danger">*</span></label>
                            <p class="text-muted fs-12 mb-2" id="createOptionsHint"></p>

                            {{-- Switch: exactly 2 fixed inputs --}}
                            <div id="createSwitchSection" style="display:none;">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="text" name="options[]" id="createSwOpt1"
                                               class="form-control" placeholder="Option 1 (e.g. Yes)"
                                               maxlength="100" disabled>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="options[]" id="createSwOpt2"
                                               class="form-control" placeholder="Option 2 (e.g. No)"
                                               maxlength="100" disabled>
                                    </div>
                                </div>
                            </div>

                            {{-- Option List: dynamic inputs --}}
                            <div id="createListSection" style="display:none;">
                                <div id="createOptionsList"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                        onclick="addCreateOption()">
                                    <i class="ri-add-line me-1"></i> Add Option
                                </button>
                            </div>

                            @if($errors->has('options') || $errors->has('options.0') || $errors->has('options.1') || $errors->has('options.*'))
                            <div class="text-danger small mt-2">
                                @error('options'){{ $message }}@enderror
                                @error('options.*'){{ $message }}@enderror
                            </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active"   {{ old('status', 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="ri-add-line me-1"></i> Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== EDIT MODAL (shared) ===================== --}}
    <div class="modal fade" id="editDataTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-edit-line me-2"></i>Edit Data Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editDataTypeForm" action="">
                    @csrf @method('PUT')
                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName"
                                   class="form-control" required maxlength="255">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Data Type <span class="text-danger">*</span></label>
                            <select name="type" id="editType" class="form-select" required
                                    onchange="onEditTypeChange(this.value, [])">
                                @foreach($typeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Dynamic options section --}}
                        <div id="editOptionsWrap" style="display:none;">
                            <label class="form-label">Options <span class="text-danger">*</span></label>
                            <p class="text-muted fs-12 mb-2" id="editOptionsHint"></p>

                            {{-- Switch: exactly 2 fixed inputs --}}
                            <div id="editSwitchSection" style="display:none;">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="text" name="options[]" id="editSwOpt1"
                                               class="form-control" placeholder="Option 1 (e.g. Yes)"
                                               maxlength="100" disabled>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="options[]" id="editSwOpt2"
                                               class="form-control" placeholder="Option 2 (e.g. No)"
                                               maxlength="100" disabled>
                                    </div>
                                </div>
                            </div>

                            {{-- Option List: dynamic inputs --}}
                            <div id="editListSection" style="display:none;">
                                <div id="editOptionsList"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                        onclick="addEditOption()">
                                    <i class="ri-add-line me-1"></i> Add Option
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="editStatus" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    // ─── Helpers ────────────────────────────────────────────────────────────────

    function makeOptionRow(value, placeholder, onRemove) {
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'options[]';
        input.className = 'form-control';
        input.placeholder = placeholder;
        input.maxLength = 100;
        input.value = value || '';
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-outline-danger';
        btn.innerHTML = '<i class="ri-close-line"></i>';
        btn.onclick = onRemove;
        div.appendChild(input);
        div.appendChild(btn);
        return div;
    }

    function renumberPlaceholders(containerId) {
        document.querySelectorAll('#' + containerId + ' input[name="options[]"]').forEach((inp, i) => {
            inp.placeholder = 'Option ' + (i + 1);
        });
    }

    // ─── CREATE modal ───────────────────────────────────────────────────────────

    function onCreateTypeChange(type) {
        const wrap      = document.getElementById('createOptionsWrap');
        const swSection = document.getElementById('createSwitchSection');
        const lstSection= document.getElementById('createListSection');
        const hint      = document.getElementById('createOptionsHint');

        // disable everything first so disabled inputs don't submit
        document.getElementById('createSwOpt1').disabled = true;
        document.getElementById('createSwOpt2').disabled = true;
        document.querySelectorAll('#createOptionsList input').forEach(i => i.disabled = true);

        swSection.style.display  = 'none';
        lstSection.style.display = 'none';
        wrap.style.display       = 'none';

        if (type === 'switch') {
            hint.textContent = 'Enter exactly 2 options (e.g. Yes / No, Pass / Fail).';
            wrap.style.display      = '';
            swSection.style.display = '';
            document.getElementById('createSwOpt1').disabled = false;
            document.getElementById('createSwOpt2').disabled = false;

        } else if (type === 'option_list') {
            hint.textContent = 'Enter at least 2 options. Use "Add Option" to add more.';
            wrap.style.display       = '';
            lstSection.style.display = '';
            // seed 2 rows if empty
            const list = document.getElementById('createOptionsList');
            if (list.children.length === 0) {
                addCreateOption(); addCreateOption();
            }
            document.querySelectorAll('#createOptionsList input').forEach(i => i.disabled = false);
        }
    }

    function addCreateOption() {
        const list  = document.getElementById('createOptionsList');
        const index = list.children.length + 1;
        const row   = makeOptionRow('', 'Option ' + index, function() {
            if (list.children.length <= 2) return;
            this.closest('.input-group').remove();
            renumberPlaceholders('createOptionsList');
        });
        row.querySelector('input').disabled = false;
        list.appendChild(row);
    }

    // ─── EDIT modal ─────────────────────────────────────────────────────────────

    function onEditTypeChange(type, prefill) {
        prefill = prefill || [];
        const wrap      = document.getElementById('editOptionsWrap');
        const swSection = document.getElementById('editSwitchSection');
        const lstSection= document.getElementById('editListSection');
        const hint      = document.getElementById('editOptionsHint');

        document.getElementById('editSwOpt1').disabled = true;
        document.getElementById('editSwOpt2').disabled = true;
        document.querySelectorAll('#editOptionsList input').forEach(i => i.disabled = true);

        swSection.style.display  = 'none';
        lstSection.style.display = 'none';
        wrap.style.display       = 'none';

        if (type === 'switch') {
            hint.textContent = 'Enter exactly 2 options (e.g. Yes / No, Pass / Fail).';
            wrap.style.display      = '';
            swSection.style.display = '';
            const o1 = document.getElementById('editSwOpt1');
            const o2 = document.getElementById('editSwOpt2');
            o1.disabled = false; o1.value = prefill[0] || '';
            o2.disabled = false; o2.value = prefill[1] || '';

        } else if (type === 'option_list') {
            hint.textContent = 'Enter at least 2 options. Use "Add Option" to add more.';
            wrap.style.display       = '';
            lstSection.style.display = '';
            // rebuild list
            const list = document.getElementById('editOptionsList');
            list.innerHTML = '';
            const seed = prefill.length >= 2 ? prefill : ['', ''];
            seed.forEach((val, i) => addEditOptionWithValue(val, i + 1));
            document.querySelectorAll('#editOptionsList input').forEach(i => i.disabled = false);
        }
    }

    function addEditOptionWithValue(val, num) {
        const list  = document.getElementById('editOptionsList');
        const index = num || (list.children.length + 1);
        const row   = makeOptionRow(val, 'Option ' + index, function() {
            if (list.children.length <= 2) return;
            this.closest('.input-group').remove();
            renumberPlaceholders('editOptionsList');
        });
        row.querySelector('input').disabled = false;
        list.appendChild(row);
    }

    function addEditOption() {
        addEditOptionWithValue('');
    }

    // ─── Populate edit modal when opened ────────────────────────────────────────

    document.getElementById('editDataTypeModal').addEventListener('show.bs.modal', function(e) {
        const btn     = e.relatedTarget;
        const id      = btn.dataset.id;
        const options = JSON.parse(btn.dataset.options || '[]');

        document.getElementById('editDataTypeForm').action =
            "{{ url('admin/master/data-types') }}/" + id;

        document.getElementById('editName').value   = btn.dataset.name;
        document.getElementById('editStatus').value = btn.dataset.status;

        const typeSelect = document.getElementById('editType');
        typeSelect.value = btn.dataset.type;

        onEditTypeChange(btn.dataset.type, options);
    });

    // ─── Re-open create modal on validation error ────────────────────────────────

    @if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        @php $hasCreateErrors = $errors->has('name') || $errors->has('type') || $errors->has('status') || $errors->hasAny(['options', 'options.*', 'options.0', 'options.1']); @endphp
        @if($hasCreateErrors)
        new bootstrap.Modal(document.getElementById('createDataTypeModal')).show();
        // restore previous type selection and options
        const oldType    = "{{ old('type') }}";
        const oldOptions = @json(old('options', []));
        if (oldType) {
            document.getElementById('createType').value = oldType;
            onCreateTypeChange(oldType);
            if (oldType === 'switch') {
                if (oldOptions[0]) document.getElementById('createSwOpt1').value = oldOptions[0];
                if (oldOptions[1]) document.getElementById('createSwOpt2').value = oldOptions[1];
            } else if (oldType === 'option_list') {
                const list = document.getElementById('createOptionsList');
                list.innerHTML = '';
                oldOptions.forEach((val, i) => {
                    const row = makeOptionRow(val, 'Option ' + (i + 1), function() {
                        if (list.children.length <= 2) return;
                        this.closest('.input-group').remove();
                        renumberPlaceholders('createOptionsList');
                    });
                    row.querySelector('input').disabled = false;
                    list.appendChild(row);
                });
            }
        }
        @endif
    });
    @endif
    </script>
    @endpush
</x-app-layout>
