<x-app-layout>
    <x-slot name="title">Inspection History</x-slot>

    @php
    $resultColors = [
        'pass'           => 'success',
        'fail'           => 'danger',
        'under_review'   => 'warning',
        'restricted_use' => 'orange',
        'not_inspected'  => 'secondary',
        'not_located'    => 'dark',
    ];
    $docColors = [
        'draft'     => 'secondary',
        'submitted' => 'warning',
        'approved'  => 'success',
    ];
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Inspection History</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.assets.index') }}">Asset Register</a></li>
                        <li class="breadcrumb-item active">Inspection History</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-history-line me-2 text-primary"></i>All Inspection Records
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $records->total() }}</span>
                    </h5>
                </div>

                {{-- Filters --}}
                <div class="card-body border-bottom pb-3">
                    <form method="GET" action="{{ route('admin.assets.history') }}" class="row g-2 align-items-end" id="filterForm">
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Asset Code</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search code…" value="{{ request('search') }}">
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
                            <label class="form-label text-muted fs-12 mb-1">Asset Type</label>
                            <select name="asset_type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                @foreach($assetTypes as $val => $label)
                                <option value="{{ $val }}" {{ request('asset_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Result</label>
                            <select name="result" class="form-select form-select-sm">
                                <option value="">All Results</option>
                                @foreach(\App\Models\InspectionRecord::RESULTS as $r)
                                <option value="{{ $r }}" {{ request('result') == $r ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $r)) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <label class="form-label text-muted fs-12 mb-1">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-auto">
                            <label class="form-label text-muted fs-12 mb-1">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-search-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.assets.history') }}" class="btn btn-light btn-sm ms-1">
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
                                    <th>Asset</th>
                                    <th>Type</th>
                                    <th>Site / Building</th>
                                    <th>Inspection Date</th>
                                    <th>Result</th>
                                    <th>Status</th>
                                    <th>Technician</th>
                                    <th>Job</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $record)
                                @php
                                    $rc  = $resultColors[$record->result] ?? 'secondary';
                                    $dc  = $docColors[$record->document_status] ?? 'secondary';
                                    $asset = $record->asset;
                                @endphp
                                <tr>
                                    <td class="ps-3 text-muted fs-12">{{ $records->firstItem() + $loop->index }}</td>
                                    <td>
                                        @if($asset)
                                        <a href="{{ route('admin.assets.show', $asset) }}"
                                           class="fw-semibold text-primary text-decoration-none">
                                            {{ $asset->asset_code }}
                                        </a>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($asset)
                                        <span class="badge bg-info-subtle text-info fs-11">
                                            {{ $assetTypes[$asset->asset_type] ?? $asset->asset_type }}
                                        </span>
                                        @else
                                        <span class="text-muted fs-12">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($asset)
                                        <div class="fs-13">{{ $asset->site->name ?? ($asset->site->address ?? '—') }}</div>
                                        <div class="fs-11 text-muted">
                                            {{ $asset->building->name_or_level ?? '—' }}
                                            @if($asset->zone)
                                            <span>/ {{ $asset->zone }}</span>
                                            @endif
                                        </div>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="fs-13">
                                        {{ $record->inspection_date?->format('d M Y') ?? '—' }}
                                    </td>
                                    <td>
                                        @if($rc === 'orange')
                                        <span class="badge fs-11" style="background-color:#fd7e14;color:#fff;">
                                            {{ ucwords(str_replace('_', ' ', $record->result)) }}
                                        </span>
                                        @else
                                        <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }} fs-11">
                                            {{ ucwords(str_replace('_', ' ', $record->result)) }}
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $dc }}-subtle text-{{ $dc }} fs-11">
                                            {{ ucwords($record->document_status) }}
                                        </span>
                                    </td>
                                    <td class="fs-13">
                                        {{ $record->technician?->name ?? '—' }}
                                    </td>
                                    <td class="fs-13">
                                        @if($record->job)
                                        <a href="{{ route('admin.jobs.show', $record->job) }}"
                                           class="text-primary text-decoration-none">
                                            {{ $record->job->job_number ?? '#' . $record->job->id }}
                                        </a>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($asset)
                                        <a href="{{ route('admin.assets.show', $asset) }}"
                                           class="btn btn-sm btn-outline-primary" title="View Asset">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="ri-history-line fs-24 d-block mb-2 opacity-50"></i>
                                        No inspection records found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($records->hasPages())
                <div class="card-footer">
                    {{ $records->links() }}
                </div>
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
