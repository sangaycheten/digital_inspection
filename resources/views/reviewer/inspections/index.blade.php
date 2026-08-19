<x-app-layout>
    <x-slot name="title">Review Inspections</x-slot>

    @php
    $resultColors = [
        'pass'           => 'success',
        'fail'           => 'danger',
        'under_review'   => 'warning',
        'restricted_use' => 'warning',
        'not_inspected'  => 'secondary',
        'not_located'    => 'dark',
    ];
    $currentStatus = request('status', 'draft');
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Review Inspections</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('reviewer.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Inspections</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible alert-border-left fade show">
        <i class="ri-checkbox-circle-line me-3 align-middle fs-16"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible alert-border-left fade show">
        <i class="ri-alert-line me-3 align-middle fs-16"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase fw-medium text-muted fs-12 mb-1">Pending Review</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ $pendingCount }}</h4>
                        </div>
                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                            <i class="ri-survey-line text-warning"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase fw-medium text-muted fs-12 mb-1">Approved Today</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ $approvedToday }}</h4>
                        </div>
                        <span class="avatar-title bg-success-subtle rounded fs-3">
                            <i class="ri-checkbox-circle-line text-success"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">
                <i class="ri-survey-line me-2 text-primary"></i>Inspection Records
                <span class="badge bg-primary-subtle text-primary ms-1">{{ $records->total() }}</span>
            </h5>
        </div>

        {{-- Filters --}}
        <div class="card-body border-bottom pb-3">
            <form method="GET" action="{{ route('reviewer.inspections.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-muted fs-12 mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="draft"    {{ $currentStatus === 'draft'    ? 'selected' : '' }}>Pending Review</option>
                        <option value="approved" {{ $currentStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted fs-12 mb-1">Technician</label>
                    <select name="technician_id" class="form-select form-select-sm">
                        <option value="">All Technicians</option>
                        @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" {{ request('technician_id') == $tech->id ? 'selected' : '' }}>
                            {{ $tech->name }}
                        </option>
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
                    <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i>Filter</button>
                    <a href="{{ route('reviewer.inspections.index') }}" class="btn btn-light btn-sm ms-1"><i class="ri-refresh-line"></i> Reset</a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Asset</th>
                            <th>Site / Building</th>
                            <th>Result</th>
                            <th>Technician</th>
                            <th>Inspection Date</th>
                            <th>Doc Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        @php $rc = $resultColors[$record->result] ?? 'secondary'; @endphp
                        <tr>
                            <td class="ps-3 fw-medium fs-13">{{ $record->asset->asset_code }}</td>
                            <td>
                                <div class="fs-13">{{ $record->asset->site->name ?? $record->asset->site->address ?? '—' }}</div>
                                <div class="text-muted fs-12">{{ $record->asset->building?->name_or_level ?? '—' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }}">
                                    {{ ucwords(str_replace('_', ' ', $record->result)) }}
                                </span>
                            </td>
                            <td class="fs-13">{{ $record->technician->name ?? '—' }}</td>
                            <td class="fs-13 text-muted">{{ $record->inspection_date->format('d M Y') }}</td>
                            <td>
                                @if($record->document_status === 'approved')
                                <span class="badge bg-success-subtle text-success"><i class="ri-checkbox-circle-line me-1"></i>Approved</span>
                                @else
                                <span class="badge bg-warning-subtle text-warning"><i class="ri-time-line me-1"></i>Pending</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('reviewer.inspections.show', $record) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="ri-eye-line me-1"></i>Review
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ri-survey-line fs-24 d-block mb-2"></i>No inspection records found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($records->hasPages())
        <div class="card-footer">{{ $records->links() }}</div>
        @endif
    </div>

</x-app-layout>
