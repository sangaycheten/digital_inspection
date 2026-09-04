<x-app-layout>
    <x-slot name="title">Reference Data</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Reference Data</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item active">Reference Data</li>
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

    {{-- Summary Cards --}}
    <div class="row">
        @foreach(\App\Models\MasterLookup::CATEGORIES as $key => $lbl)
        <div class="col-md-4">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title rounded fs-3 bg-primary-subtle">
                                <i class="ri-list-check-2 text-primary"></i>
                            </span>
                        </div>
                        <div>
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1 fs-12">{{ $lbl }}</p>
                            <h5 class="fw-semibold mb-0">{{ $grouped->get($key, collect())->count() }} items</h5>
                        </div>
                        <div class="ms-auto">
                            <a href="{{ route('admin.master.lookups.index', ['category' => $key]) }}"
                               class="btn btn-sm btn-outline-primary {{ $category === $key ? 'active' : '' }}">
                                View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-list-check-2 me-2 text-primary"></i>
                        {{ $category ? \App\Models\MasterLookup::CATEGORIES[$category] : 'All Lookups' }}
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $lookups->total() }}</span>
                    </h5>
                    @can('add reference data')
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createLookupModal">
                        <i class="ri-add-line me-1"></i> Add Lookup
                    </button>
                    @endcan
                </div>

                <div class="card-body border-bottom pb-3">
                    <form method="GET" action="{{ route('admin.master.lookups.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label text-muted fs-12 mb-1">Category</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach(\App\Models\MasterLookup::CATEGORIES as $key => $lbl)
                                <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i> Filter</button>
                            <a href="{{ route('admin.master.lookups.index') }}" class="btn btn-light btn-sm ms-1"><i class="ri-refresh-line"></i> Reset</a>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width:50px;">#</th>
                                    <th>Category</th>
                                    <th>Label</th>
                                    <th>Code</th>
                                    <th style="width:120px;">Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="lookupTableBody">
                                @forelse($lookups as $lookup)
                                <tr id="row-{{ $lookup->id }}" data-id="{{ $lookup->id }}"
                                    data-category="{{ $lookup->category }}"
                                    data-sort="{{ $lookup->sort_order }}">
                                    <td class="ps-3 text-muted fs-12">{{ $lookups->firstItem() + $loop->index }}</td>
                                    <td>
                                        @php
                                            $catColor = match($lookup->category) {
                                                'asset_type'     => 'primary',
                                                'defect_reason'  => 'danger',
                                                'recommendation' => 'success',
                                                default          => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $catColor }}-subtle text-{{ $catColor }}">
                                            {{ \App\Models\MasterLookup::CATEGORIES[$lookup->category] ?? $lookup->category }}
                                        </span>
                                    </td>
                                    <td class="fw-medium">{{ $lookup->label }}</td>
                                    <td><span class="badge bg-light text-dark font-monospace">{{ $lookup->value ?? '—' }}</span></td>
                                    <td>
                                        <div class="hstack gap-1">
                                            <button type="button" class="btn btn-sm btn-light btn-reorder"
                                                    data-id="{{ $lookup->id }}" data-dir="up"
                                                    title="Move up">
                                                <i class="ri-arrow-up-s-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light btn-reorder"
                                                    data-id="{{ $lookup->id }}" data-dir="down"
                                                    title="Move down">
                                                <i class="ri-arrow-down-s-line"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="hstack gap-1">
                                            @can('edit reference data')
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#editLookupModal{{ $lookup->id }}">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            @endcan
                                            @can('delete reference data')
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal" data-bs-target="#deleteLookupModal{{ $lookup->id }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan
                                        </div>

                                        {{-- Edit Modal --}}
                                        <div class="modal fade" id="editLookupModal{{ $lookup->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Lookup</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="{{ route('admin.master.lookups.update', $lookup) }}">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                                                <select name="category" class="form-select" required>
                                                                    @foreach(\App\Models\MasterLookup::CATEGORIES as $key => $lbl)
                                                                    <option value="{{ $key }}" {{ $lookup->category === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Label <span class="text-danger">*</span></label>
                                                                <input type="text" name="label" class="form-control"
                                                                       value="{{ $lookup->label }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Code</label>
                                                                <input type="text" name="value" class="form-control font-monospace"
                                                                       value="{{ $lookup->value }}">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Save</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Delete Modal --}}
                                        <div class="modal fade" id="deleteLookupModal{{ $lookup->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="avatar-sm mx-auto mb-3">
                                                            <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-22">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </span>
                                                        </div>
                                                        <h5 class="mb-3">Delete Lookup</h5>
                                                        <p class="text-muted mb-4">Are you sure you want to delete <strong>{{ $lookup->label }}</strong>?</p>
                                                        <div class="hstack gap-2 justify-content-center">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST" action="{{ route('admin.master.lookups.destroy', $lookup) }}">
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
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="ri-list-check-2 fs-24 d-block mb-2"></i>No lookup values found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($lookups->hasPages())
                <div class="card-footer">{{ $lookups->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createLookupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-list-check-2 me-2"></i>Add Lookup Value</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.master.lookups.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="">-- Select Category --</option>
                                @foreach(\App\Models\MasterLookup::CATEGORIES as $key => $lbl)
                                <option value="{{ $key }}" {{ old('category', $category) === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Label <span class="text-danger">*</span></label>
                            <input type="text" name="label" id="createLookupLabel"
                                   class="form-control @error('label') is-invalid @enderror"
                                   value="{{ old('label') }}" required>
                            @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Code</label>
                            <input type="text" name="value" id="createLookupCode"
                                   class="form-control font-monospace @error('value') is-invalid @enderror"
                                   value="{{ old('value') }}">
                            @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="ri-add-line me-1"></i> Add Lookup</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    // Auto-generate code from label
    (function () {
        const labelEl = document.getElementById('createLookupLabel');
        const codeEl  = document.getElementById('createLookupCode');
        if (!labelEl || !codeEl) return;
        let userEdited = !!codeEl.value;
        codeEl.addEventListener('input', () => { userEdited = true; });
        labelEl.addEventListener('input', function () {
            if (userEdited) return;
            codeEl.value = labelEl.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
        });
    })();

    // ↑/↓ reorder
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;
    document.querySelectorAll('.btn-reorder').forEach(btn => {
        btn.addEventListener('click', function () {
            const id  = this.dataset.id;
            const dir = this.dataset.dir;
            this.disabled = true;
            fetch(`/admin/master/lookups/${id}/reorder`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ direction: dir }),
            })
            .then(r => r.json())
            .then(data => { if (data.ok) location.reload(); else this.disabled = false; })
            .catch(() => { this.disabled = false; });
        });
    });

    @if($errors->has('category') || $errors->has('label') || $errors->has('value'))
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('createLookupModal')).show();
    });
    @endif
    </script>
    @endpush

</x-app-layout>
