<x-app-layout>
    <x-slot name="title">Sites</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Sites</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item active">Sites</li>
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
                        <i class="ri-map-pin-line me-2 text-primary"></i>All Sites
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $sites->total() }}</span>
                    </h5>
                    @if($clients->isNotEmpty())
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createSiteModal">
                        <i class="ri-add-line me-1"></i> Add Site
                    </button>
                    @endif
                </div>

                <div class="card-body border-bottom pb-3">
                    <form method="GET" action="{{ route('admin.master.sites.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label text-muted fs-12 mb-1">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search by address..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted fs-12 mb-1">Client</label>
                            <select name="client_id" class="form-select form-select-sm">
                                <option value="">All Clients</option>
                                @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i> Filter</button>
                            <a href="{{ route('admin.master.sites.index') }}" class="btn btn-light btn-sm ms-1"><i class="ri-refresh-line"></i> Reset</a>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Client</th>
                                    <th>Address</th>
                                    <th>Site Notes</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sites as $site)
                                <tr>
                                    <td class="ps-3 text-muted fs-12">{{ $sites->firstItem() + $loop->index }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">{{ $site->client->custom_client_code ?? '—' }}</span>
                                        <span class="ms-1 fs-13">{{ $site->client->name ?? '—' }}</span>
                                    </td>
                                    <td>{{ $site->address }}</td>
                                    <td class="text-muted fs-12">{{ Str::limit($site->site_notes, 50) ?? '—' }}</td>
                                    <td class="text-muted fs-12">{{ $site->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="hstack gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#editSiteModal{{ $site->id }}">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal" data-bs-target="#deleteSiteModal{{ $site->id }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>

                                        {{-- Edit Modal --}}
                                        <div class="modal fade" id="editSiteModal{{ $site->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Site</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="{{ route('admin.master.sites.update', $site) }}">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Client <span class="text-danger">*</span></label>
                                                                <select name="client_id" class="form-select" required>
                                                                    @foreach($clients as $client)
                                                                    <option value="{{ $client->id }}" {{ $site->client_id == $client->id ? 'selected' : '' }}>
                                                                        {{ $client->name }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Address <span class="text-danger">*</span></label>
                                                                <textarea name="address" class="form-control" rows="2" required>{{ $site->address }}</textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Site Notes</label>
                                                                <textarea name="site_notes" class="form-control" rows="3">{{ $site->site_notes }}</textarea>
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
                                        <div class="modal fade" id="deleteSiteModal{{ $site->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="avatar-sm mx-auto mb-3">
                                                            <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-22">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </span>
                                                        </div>
                                                        <h5 class="mb-3">Delete Site</h5>
                                                        <p class="text-muted mb-4">Are you sure you want to delete this site? All buildings under it will also be removed.</p>
                                                        <div class="hstack gap-2 justify-content-center">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST" action="{{ route('admin.master.sites.destroy', $site) }}">
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
                                        <i class="ri-map-pin-line fs-24 d-block mb-2"></i>No sites found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($sites->hasPages())
                <div class="card-footer">{{ $sites->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createSiteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-map-pin-line me-2"></i>Add New Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.master.sites.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                                <option value="">-- Select Client --</option>
                                @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2" required>{{ old('address') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Site Notes</label>
                            <textarea name="site_notes" class="form-control" rows="3" placeholder="Any notes about this site...">{{ old('site_notes') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="ri-add-line me-1"></i> Create Site</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    @if($errors->has('client_id') || $errors->has('address'))
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('createSiteModal')).show();
    });
    @endif
    </script>
    @endpush

</x-app-layout>
