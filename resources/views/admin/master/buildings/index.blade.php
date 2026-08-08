<x-app-layout>
    <x-slot name="title">Buildings</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Buildings</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item active">Buildings</li>
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
                        <i class="ri-home-office-line me-2 text-primary"></i>All Buildings
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $buildings->total() }}</span>
                    </h5>
                    @if($sites->isNotEmpty())
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createBuildingModal">
                        <i class="ri-add-line me-1"></i> Add Building
                    </button>
                    @endif
                </div>

                <div class="card-body border-bottom pb-3">
                    <form method="GET" action="{{ route('admin.master.buildings.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label text-muted fs-12 mb-1">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search by name or level..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted fs-12 mb-1">Site</label>
                            <select name="site_id" class="form-select form-select-sm">
                                <option value="">All Sites</option>
                                @foreach($sites as $site)
                                <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                    {{ $site->client->name ?? '' }} — {{ Str::limit($site->address, 40) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i> Filter</button>
                            <a href="{{ route('admin.master.buildings.index') }}" class="btn btn-light btn-sm ms-1"><i class="ri-refresh-line"></i> Reset</a>
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
                                    <th>Site Address</th>
                                    <th>Building / Level</th>
                                    <th>Roof Zones</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($buildings as $building)
                                <tr>
                                    <td class="ps-3 text-muted fs-12">{{ $buildings->firstItem() + $loop->index }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary fs-11">
                                            {{ $building->site->client->custom_client_code ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="text-muted fs-12">{{ Str::limit($building->site->address ?? '—', 40) }}</td>
                                    <td class="fw-medium">{{ $building->name_or_level }}</td>
                                    <td>
                                        @if($building->roof_zones)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($building->roof_zones as $zone)
                                                    <span class="badge bg-light text-dark">{{ $zone }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted fs-12">{{ $building->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="hstack gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#editBuildingModal{{ $building->id }}">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal" data-bs-target="#deleteBuildingModal{{ $building->id }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>

                                        {{-- Edit Modal --}}
                                        <div class="modal fade" id="editBuildingModal{{ $building->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Building</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="{{ route('admin.master.buildings.update', $building) }}">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Site <span class="text-danger">*</span></label>
                                                                <select name="site_id" class="form-select" required>
                                                                    @foreach($sites as $site)
                                                                    <option value="{{ $site->id }}" {{ $building->site_id == $site->id ? 'selected' : '' }}>
                                                                        {{ $site->client->name ?? '' }} — {{ Str::limit($site->address, 50) }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Building / Level Name <span class="text-danger">*</span></label>
                                                                <input type="text" name="name_or_level" class="form-control"
                                                                       value="{{ $building->name_or_level }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Roof Zones</label>
                                                                <input type="text" name="roof_zones" class="form-control"
                                                                       value="{{ $building->roof_zones ? implode(', ', $building->roof_zones) : '' }}"
                                                                       placeholder="e.g. Zone A, Zone B, Zone C">
                                                                <div class="form-text">Comma-separated list of roof zones.</div>
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
                                        <div class="modal fade" id="deleteBuildingModal{{ $building->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="avatar-sm mx-auto mb-3">
                                                            <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-22">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </span>
                                                        </div>
                                                        <h5 class="mb-3">Delete Building</h5>
                                                        <p class="text-muted mb-4">Are you sure you want to delete <strong>{{ $building->name_or_level }}</strong>?</p>
                                                        <div class="hstack gap-2 justify-content-center">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST" action="{{ route('admin.master.buildings.destroy', $building) }}">
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
                                        <i class="ri-home-office-line fs-24 d-block mb-2"></i>No buildings found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($buildings->hasPages())
                <div class="card-footer">{{ $buildings->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createBuildingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-home-office-line me-2"></i>Add New Building</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.master.buildings.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Site <span class="text-danger">*</span></label>
                            <select name="site_id" class="form-select @error('site_id') is-invalid @enderror" required>
                                <option value="">-- Select Site --</option>
                                @foreach($sites as $site)
                                <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                    {{ $site->client->name ?? '' }} — {{ Str::limit($site->address, 50) }}
                                </option>
                                @endforeach
                            </select>
                            @error('site_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Building / Level Name <span class="text-danger">*</span></label>
                            <input type="text" name="name_or_level" class="form-control @error('name_or_level') is-invalid @enderror"
                                   value="{{ old('name_or_level') }}" required placeholder="e.g. Level 1, Roof Top">
                            @error('name_or_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Roof Zones</label>
                            <input type="text" name="roof_zones" class="form-control"
                                   value="{{ old('roof_zones') }}" placeholder="e.g. Zone A, Zone B, Zone C">
                            <div class="form-text">Comma-separated list of roof zones.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="ri-add-line me-1"></i> Create Building</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    @if($errors->has('site_id') || $errors->has('name_or_level'))
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('createBuildingModal')).show();
    });
    @endif
    </script>
    @endpush

</x-app-layout>
