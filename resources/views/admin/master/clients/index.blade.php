<x-app-layout>
    <x-slot name="title">Clients</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Clients</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item active">Clients</li>
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
                        <i class="ri-building-2-line me-2 text-primary"></i>All Clients
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $clients->total() }}</span>
                    </h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createClientModal">
                        <i class="ri-add-line me-1"></i> Add Client
                    </button>
                </div>

                {{-- Filters --}}
                <div class="card-body border-bottom pb-3">
                    <form method="GET" action="{{ route('admin.master.clients.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label text-muted fs-12 mb-1">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search by name or client code..."
                                   value="{{ request('search') }}">
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
                            <a href="{{ route('admin.master.clients.index') }}" class="btn btn-light btn-sm ms-1">
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
                                    <th class="ps-3">#</th>
                                    <th>Client Name</th>
                                    <th>Code</th>
                                    <th>Billing Contact</th>
                                    <th>Sites</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clients as $client)
                                <tr>
                                    <td class="ps-3 text-muted fs-12">{{ $clients->firstItem() + $loop->index }}</td>
                                    <td class="fw-medium">{{ $client->name }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $client->custom_client_code }}</span></td>
                                    <td class="text-muted fs-12">{{ Str::limit($client->billing_contact_info, 40) ?? '—' }}</td>
                                    <td><span class="badge bg-primary-subtle text-primary">{{ $client->sites_count }}</span></td>
                                    <td>
                                        @if($client->status === 'active')
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-muted fs-12">{{ $client->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="hstack gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#editClientModal{{ $client->id }}">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal" data-bs-target="#deleteClientModal{{ $client->id }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>

                                        {{-- Edit Modal --}}
                                        <div class="modal fade" id="editClientModal{{ $client->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Client</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="{{ route('admin.master.clients.update', $client) }}">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                                                <input type="text" name="name" class="form-control" value="{{ $client->name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Client Code <span class="text-danger">*</span></label>
                                                                <input type="text" name="custom_client_code" class="form-control" value="{{ $client->custom_client_code }}" required maxlength="20">
                                                                <div class="form-text">Short unique code, e.g. RPH, WST</div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Billing Contact Info</label>
                                                                <textarea name="billing_contact_info" class="form-control" rows="3">{{ $client->billing_contact_info }}</textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                                                <select name="status" class="form-select" required>
                                                                    <option value="active"   {{ $client->status === 'active'   ? 'selected' : '' }}>Active</option>
                                                                    <option value="inactive" {{ $client->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                                </select>
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
                                        <div class="modal fade" id="deleteClientModal{{ $client->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="avatar-sm mx-auto mb-3">
                                                            <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-22">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </span>
                                                        </div>
                                                        <h5 class="mb-3">Delete Client</h5>
                                                        <p class="text-muted mb-4">Are you sure you want to delete <strong>{{ $client->name }}</strong>?</p>
                                                        <div class="hstack gap-2 justify-content-center">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST" action="{{ route('admin.master.clients.destroy', $client) }}">
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
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="ri-building-2-line fs-24 d-block mb-2"></i>No clients found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($clients->hasPages())
                <div class="card-footer">{{ $clients->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createClientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-building-2-line me-2"></i>Add New Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.master.clients.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Client Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client Code <span class="text-danger">*</span></label>
                            <input type="text" name="custom_client_code" class="form-control @error('custom_client_code') is-invalid @enderror"
                                   value="{{ old('custom_client_code') }}" required maxlength="20" placeholder="e.g. RPH">
                            <div class="form-text">Short unique code used across the system.</div>
                            @error('custom_client_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Billing Contact Info</label>
                            <textarea name="billing_contact_info" class="form-control" rows="3"
                                      placeholder="Contact name, email, phone...">{{ old('billing_contact_info') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active"   {{ old('status', 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="ri-add-line me-1"></i> Create Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    @if($errors->has('name') || $errors->has('custom_client_code') || $errors->has('status'))
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('createClientModal')).show();
    });
    @endif
    </script>
    @endpush

</x-app-layout>
