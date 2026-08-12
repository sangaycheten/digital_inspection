<x-app-layout>
    <x-slot name="title">Job Details</x-slot>

    @php
    $statusColors = [
        'new'                    => 'secondary',
        'scheduled'              => 'info',
        'in_progress'            => 'primary',
        'submitted_for_review'   => 'warning',
        'under_review'           => 'warning',
        'approved'               => 'success',
        'issued'                 => 'success',
        'rectification_required' => 'danger',
        'closed'                 => 'dark',
    ];
    $resultColors = [
        'pass'           => 'success',
        'fail'           => 'danger',
        'under_review'   => 'warning',
        'restricted_use' => 'warning',
        'not_inspected'  => 'secondary',
        'not_located'    => 'dark',
    ];
    $sc = $statusColors[$job->status] ?? 'secondary';
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">
                    Job — <span class="text-primary">{{ \App\Models\Job::WORK_TYPES[$job->work_type] }}</span>
                    <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} ms-2 fs-12">
                        {{ \App\Models\Job::STATUSES[$job->status] }}
                    </span>
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.jobs.index') }}">Jobs</a></li>
                        <li class="breadcrumb-item active">Details</li>
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

    <div class="row g-3">

        {{-- Left: tabs --}}
        <div class="col-lg-8">

            <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-details" role="tab">
                        <i class="ri-briefcase-line me-1"></i> Job Details
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-assets" role="tab">
                        <i class="ri-tools-line me-1"></i> Assets
                        <span class="badge bg-primary-subtle text-primary ms-1">
                            {{ $job->targetAssets->count() + $job->installationAssets->count() + $job->inspectionRecords->count() }}
                        </span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                {{-- Details Tab --}}
                <div class="tab-pane fade show active" id="tab-details">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-3">Location</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted ps-0 fs-13" style="width:120px">Client</td>
                                            <td class="fw-medium fs-13">{{ $job->client->name ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Site</td>
                                            <td class="fs-13">{{ $job->site->name ?? $job->site->address }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Buildings</td>
                                            <td class="fs-13">
                                                @forelse($job->buildings as $b)
                                                <span class="badge bg-light text-dark border me-1">{{ $b->name_or_level }}</span>
                                                @empty
                                                <span class="text-muted">—</span>
                                                @endforelse
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-3">Schedule</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted ps-0 fs-13" style="width:120px">Work Type</td>
                                            <td class="fs-13">
                                                <span class="badge bg-info-subtle text-info">
                                                    {{ \App\Models\Job::WORK_TYPES[$job->work_type] }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Scheduled</td>
                                            <td class="fs-13">{{ $job->scheduled_date?->format('d M Y') ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Created</td>
                                            <td class="fs-13">{{ $job->created_at->format('d M Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Created by</td>
                                            <td class="fs-13">{{ $job->creator->name ?? '—' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                @if($job->scope_notes)
                                <div class="col-12">
                                    <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-2">Scope Notes</h6>
                                    <p class="fs-13 mb-0">{{ $job->scope_notes }}</p>
                                </div>
                                @endif

                                {{-- Technicians --}}
                                <div class="col-12">
                                    <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-2">Assigned Technicians</h6>
                                    @forelse($job->technicians as $tech)
                                    <span class="badge bg-primary-subtle text-primary me-1 mb-1 fs-12 px-2 py-1">
                                        <i class="ri-user-line me-1"></i>{{ $tech->name }}
                                    </span>
                                    @empty
                                    <span class="text-muted fs-13">None assigned.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Assets Tab --}}
                <div class="tab-pane fade" id="tab-assets">

                    {{-- Target Assets --}}
                    @if($job->targetAssets->isNotEmpty())
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="ri-list-check-3 me-2 text-primary"></i>Target Assets
                                @php $done = $job->targetAssets->where('completed', true)->count(); $total = $job->targetAssets->count(); @endphp
                                <span class="badge bg-success-subtle text-success ms-1">{{ $done }} / {{ $total }} completed</span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Asset Code</th>
                                            <th>Type</th>
                                            <th>Zone</th>
                                            <th>Completed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($job->targetAssets as $ta)
                                        <tr>
                                            <td class="ps-3">
                                                <a href="{{ route('admin.assets.show', $ta->asset) }}"
                                                   class="fw-medium text-primary text-decoration-none">
                                                    {{ $ta->asset->asset_code }}
                                                </a>
                                            </td>
                                            <td class="fs-13">{{ $ta->asset->asset_type }}</td>
                                            <td class="fs-13 text-muted">{{ $ta->asset->zone ?? '—' }}</td>
                                            <td>
                                                @if($ta->completed)
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="ri-checkbox-circle-line me-1"></i>Done
                                                    {{ $ta->completed_at?->format('d M Y') }}
                                                </span>
                                                @else
                                                <span class="badge bg-secondary-subtle text-secondary">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Installation Assets --}}
                    @if($job->installationAssets->isNotEmpty())
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0"><i class="ri-tools-line me-2 text-primary"></i>Installation / Rectification</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Asset Code</th>
                                            <th>Action</th>
                                            <th>Material Notes</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($job->installationAssets as $ia)
                                        <tr>
                                            <td class="ps-3">
                                                <a href="{{ route('admin.assets.show', $ia->asset) }}"
                                                   class="fw-medium text-primary text-decoration-none">
                                                    {{ $ia->asset->asset_code }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info">{{ ucfirst($ia->action) }}</span>
                                            </td>
                                            <td class="fs-13 text-muted">{{ $ia->material_notes ?? '—' }}</td>
                                            <td class="fs-13 text-muted">{{ $ia->created_at->format('d M Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Inspection Records --}}
                    @if($job->inspectionRecords->isNotEmpty())
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0"><i class="ri-survey-line me-2 text-primary"></i>Inspection Records</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Asset</th>
                                            <th>Date</th>
                                            <th>Result</th>
                                            <th>Status</th>
                                            <th>Technician</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($job->inspectionRecords as $ir)
                                        @php $rc = $resultColors[$ir->result] ?? 'secondary'; @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <a href="{{ route('admin.assets.show', $ir->asset) }}"
                                                   class="fw-medium text-primary text-decoration-none">
                                                    {{ $ir->asset->asset_code }}
                                                </a>
                                            </td>
                                            <td class="fs-13">{{ $ir->inspection_date->format('d M Y') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }}">
                                                    {{ ucwords(str_replace('_', ' ', $ir->result)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($ir->document_status === 'approved')
                                                <span class="badge bg-success-subtle text-success">Approved</span>
                                                @else
                                                <span class="badge bg-warning-subtle text-warning">Draft</span>
                                                @endif
                                            </td>
                                            <td class="fs-13">{{ $ir->technician->name ?? '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($job->targetAssets->isEmpty() && $job->installationAssets->isEmpty() && $job->inspectionRecords->isEmpty())
                    <div class="card">
                        <div class="card-body text-center text-muted py-5">
                            <i class="ri-tools-line fs-24 d-block mb-2"></i>
                            No assets linked to this job yet.
                        </div>
                    </div>
                    @endif

                </div>

            </div>
        </div>

        {{-- Right sidebar --}}
        <div class="col-lg-4">

            {{-- Status timeline --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="ri-git-branch-line me-2 text-primary"></i>Workflow</h6>
                </div>
                <div class="card-body">
                    @php
                    $allStatuses = array_keys(\App\Models\Job::STATUSES);
                    $currentIdx  = array_search($job->status, $allStatuses);
                    @endphp
                    <ul class="list-unstyled mb-0 vstack gap-2">
                        @foreach(\App\Models\Job::STATUSES as $sKey => $sLabel)
                        @php
                        $sIdx   = array_search($sKey, $allStatuses);
                        $isPast = $sIdx < $currentIdx;
                        $isCurr = $sKey === $job->status;
                        $sc2    = $statusColors[$sKey] ?? 'secondary';
                        @endphp
                        <li class="d-flex align-items-center gap-2 fs-13 {{ $isCurr ? 'fw-semibold' : ($isPast ? 'text-muted' : 'text-muted opacity-50') }}">
                            @if($isCurr)
                            <i class="ri-record-circle-line text-{{ $sc2 }}"></i>
                            @elseif($isPast)
                            <i class="ri-checkbox-circle-fill text-success"></i>
                            @else
                            <i class="ri-circle-line text-muted"></i>
                            @endif
                            {{ $sLabel }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Actions --}}
            @if(!$job->isClosed())
            <a href="{{ route('admin.jobs.edit', $job) }}" class="btn btn-primary w-100">
                <i class="ri-edit-line me-1"></i> Edit / Advance Status
            </a>
            @else
            <div class="alert alert-secondary fs-13 d-flex align-items-center gap-2">
                <i class="ri-lock-line fs-18"></i> This job is closed.
            </div>
            @endif

        </div>

    </div>

</x-app-layout>
