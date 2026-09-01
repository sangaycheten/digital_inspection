<x-app-layout>
    <x-slot name="title">My Assets</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">My Assets</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">My Assets</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom pb-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-muted fs-12 mb-1">Site</label>
                    <select name="site_id" class="form-select form-select-sm">
                        <option value="">All Sites</option>
                        @foreach($sites as $site)
                        <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                            {{ $site->name ?? $site->address }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted fs-12 mb-1">Asset Type</label>
                    <select name="asset_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach($assetTypes as $val => $label)
                        <option value="{{ $val }}" {{ request('asset_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted fs-12 mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach($statusOptions as $val => $label)
                        <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted fs-12 mb-1">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Asset code…" value="{{ request('search') }}">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i>Filter</button>
                    <a href="{{ route('client.assets.index') }}" class="btn btn-light btn-sm ms-1"><i class="ri-refresh-line"></i></a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Asset Code</th>
                            <th>Type</th>
                            <th>Site</th>
                            <th>Building / Zone</th>
                            <th>Status</th>
                            <th>Next Inspection Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                        @php
                            $sc = match($asset->current_status) {
                                'pass'           => 'success',
                                'fail'           => 'danger',
                                'restricted_use' => 'warning',
                                'not_inspected'  => 'secondary',
                                'not_located'    => 'dark',
                                'removed'        => 'danger',
                                default          => 'secondary',
                            };
                            $due = $asset->next_inspection_due_date;
                            $daysLeft = $due ? now()->diffInDays($due, false) : null;
                        @endphp
                        <tr>
                            <td class="ps-3 fw-medium">
                                <a href="{{ route('client.assets.show', $asset) }}" class="text-primary">
                                    {{ $asset->asset_code }}
                                </a>
                            </td>
                            <td class="fs-13 text-muted">{{ $assetTypes[$asset->asset_type] ?? $asset->asset_type }}</td>
                            <td class="fs-13 text-muted">{{ $asset->site->name ?? $asset->site->address ?? '—' }}</td>
                            <td class="fs-13 text-muted">
                                {{ $asset->building?->name_or_level ?? '—' }}
                                @if($asset->zone) <span class="text-muted">· {{ $asset->zone }}</span> @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }}">
                                    {{ ucwords(str_replace('_', ' ', $asset->current_status ?? 'not_inspected')) }}
                                </span>
                            </td>
                            <td class="fs-13">
                                @if($due)
                                    <span class="{{ $daysLeft !== null && $daysLeft <= 30 ? 'text-danger fw-medium' : 'text-muted' }}">
                                        {{ $due->format('d M Y') }}
                                        @if($daysLeft !== null && $daysLeft <= 0)
                                            <span class="badge bg-danger-subtle text-danger ms-1">Overdue</span>
                                        @elseif($daysLeft !== null && $daysLeft <= 30)
                                            <span class="badge bg-warning-subtle text-warning ms-1">{{ $daysLeft }}d</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="ri-tools-line fs-2 opacity-50 d-block mb-2"></i>No assets found.
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
</x-app-layout>
