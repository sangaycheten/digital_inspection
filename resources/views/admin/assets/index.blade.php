<x-app-layout>
    <x-slot name="title">Asset Register</x-slot>

    @php
    $statusColors = [
        'pass'           => 'success',
        'fail'           => 'danger',
        'under_review'   => 'warning',
        'restricted_use' => 'orange',
        'not_inspected'  => 'secondary',
        'not_located'    => 'dark',
        'removed'        => 'dark',
        'replaced'       => 'info',
    ];
    $typeLabels = [
        'anchor_point' => 'Anchor Point',
        'static_line'  => 'Static Line',
        'ladder'       => 'Ladder',
        'guardrail'    => 'Guardrail',
        'walkway'      => 'Walkway',
        'other'        => 'Other',
    ];
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Asset Register</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Asset Register</li>
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
                        <i class="ri-tools-line me-2 text-primary"></i>All Assets
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $assets->total() }}</span>
                    </h5>
                    <a href="{{ route('admin.assets.create') }}" class="btn btn-sm btn-primary">
                        <i class="ri-add-line me-1"></i> Add Asset
                    </a>
                </div>

                {{-- Filters --}}
                <div class="card-body border-bottom pb-3">
                    <form method="GET" action="{{ route('admin.assets.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label text-muted fs-12 mb-1">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Code, make, model, serial…" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Site</label>
                            <select name="site_id" class="form-select form-select-sm" id="filterSite">
                                <option value="">All Sites</option>
                                @foreach($sites as $site)
                                <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                    {{ $site->name ?? $site->address }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Building</label>
                            <select name="building_id" class="form-select form-select-sm" id="filterBuilding">
                                <option value="">All Buildings</option>
                                @foreach($buildings as $building)
                                <option value="{{ $building->id }}" {{ request('building_id') == $building->id ? 'selected' : '' }}>
                                    {{ $building->name_or_level }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Type</label>
                            <select name="asset_type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                @foreach($typeLabels as $val => $label)
                                <option value="{{ $val }}" {{ request('asset_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                @foreach(\App\Models\Asset::STATUSES as $s)
                                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $s)) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-search-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.assets.index') }}" class="btn btn-light btn-sm ms-1">
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
                                    <th>Code</th>
                                    <th>Type</th>
                                    <th>Site</th>
                                    <th>Building / Zone</th>
                                    <th>Make / Model</th>
                                    <th>Status</th>
                                    <th>Next Inspection</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assets as $asset)
                                @php
                                    $color = $statusColors[$asset->current_status] ?? 'secondary';
                                    $due = $asset->next_inspection_due_date;
                                    $dueClass = $due && $due->isPast() ? 'text-danger fw-semibold' : ($due && $due->diffInDays(now()) <= 30 ? 'text-warning fw-semibold' : 'text-muted');
                                @endphp
                                <tr>
                                    <td class="ps-3 text-muted fs-12">{{ $assets->firstItem() + $loop->index }}</td>
                                    <td>
                                        <a href="{{ route('admin.assets.show', $asset) }}" class="fw-semibold text-primary text-decoration-none">
                                            {{ $asset->asset_code }}
                                        </a>
                                        @if($asset->group_id)
                                        <span class="ms-1 badge bg-light text-muted border fs-10" title="Group: {{ $asset->group_id }}">
                                            <i class="ri-stack-line"></i> Group
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info">
                                            {{ $typeLabels[$asset->asset_type] ?? $asset->asset_type }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fs-13">{{ $asset->site->name ?? $asset->site->address }}</div>
                                        <div class="fs-11 text-muted">{{ $asset->site->client->name ?? '—' }}</div>
                                    </td>
                                    <td class="fs-13">
                                        {{ $asset->building->name_or_level ?? '—' }}
                                        @if($asset->zone)
                                        <span class="text-muted"> / {{ $asset->zone }}</span>
                                        @endif
                                    </td>
                                    <td class="fs-13">
                                        {{ $asset->make ?? '—' }}
                                        @if($asset->model)
                                        <span class="text-muted"> / {{ $asset->model }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($color === 'orange')
                                        <span class="badge" style="background-color:#fd7e14;color:#fff;">
                                            {{ ucwords(str_replace('_', ' ', $asset->current_status)) }}
                                        </span>
                                        @else
                                        <span class="badge bg-{{ $color }}-subtle text-{{ $color }}">
                                            {{ ucwords(str_replace('_', ' ', $asset->current_status)) }}
                                        </span>
                                        @endif
                                    </td>
                                    <td class="{{ $dueClass }} fs-12">
                                        {{ $due ? $due->format('d M Y') : '—' }}
                                    </td>
                                    <td>
                                        <div class="hstack gap-1">
                                            <a href="{{ route('admin.assets.show', $asset) }}"
                                               class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="{{ route('admin.assets.edit', $asset) }}"
                                               class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="ri-tools-line fs-24 d-block mb-2"></i>No assets found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($assets->hasPages())
                <div class="card-footer">{{ $assets->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.getElementById('filterSite').addEventListener('change', function () {
        this.closest('form').submit();
    });
    </script>
    @endpush

</x-app-layout>
