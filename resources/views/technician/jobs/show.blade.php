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
    $isInspectionJob   = in_array($job->work_type, ['first_inspection', 're_inspection']);
    $isInstallationJob = in_array($job->work_type, ['installation', 'rectification', 'combined']);
    $canCapture        = !$job->isClosed() && in_array($job->status, ['scheduled', 'in_progress', 'rectification_required']);
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">
                    {{ \App\Models\Job::WORK_TYPES[$job->work_type] }}
                    <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} ms-2 fs-12">
                        {{ \App\Models\Job::STATUSES[$job->status] }}
                    </span>
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('technician.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('technician.jobs.index') }}">My Jobs</a></li>
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

        {{-- Left column --}}
        <div class="col-lg-8">

            {{-- Job summary card --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-3">Location</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted ps-0 fs-13" style="width:110px">Client</td>
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
                                        @empty <span class="text-muted">All buildings</span>
                                        @endforelse
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-3">Schedule</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted ps-0 fs-13" style="width:110px">Work Type</td>
                                    <td class="fs-13">
                                        <span class="badge bg-info-subtle text-info">{{ \App\Models\Job::WORK_TYPES[$job->work_type] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 fs-13">Scheduled</td>
                                    <td class="fs-13">{{ $job->scheduled_date?->format('d M Y') ?? '—' }}</td>
                                </tr>
                                @if($job->scope_notes)
                                <tr>
                                    <td class="text-muted ps-0 fs-13">Scope</td>
                                    <td class="fs-13">{{ $job->scope_notes }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Capture actions --}}
            @if($canCapture)
            <div class="card mb-3 border border-primary-subtle">
                <div class="card-header bg-primary-subtle">
                    <h6 class="card-title mb-0 text-primary">
                        <i class="ri-camera-line me-2"></i>Capture
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @if($isInspectionJob || $job->work_type === 'combined')
                        <a href="{{ route('technician.jobs.inspect', $job) }}"
                           class="btn btn-primary">
                            <i class="ri-survey-line me-1"></i>
                            Record Inspections
                            @if($inspectedCount > 0)
                            <span class="badge bg-white text-primary ms-1">{{ $inspectedCount }} done</span>
                            @endif
                        </a>
                        @endif

                        @if($isInstallationJob || $job->work_type === 'combined')
                        <a href="{{ route('technician.jobs.install', $job) }}"
                           class="btn btn-outline-primary">
                            <i class="ri-tools-line me-1"></i>
                            Record Installation / Rectification
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Progress: target assets (re-inspection) --}}
            @if($job->targetAssets->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center">
                    <h6 class="card-title mb-0 flex-grow-1">
                        <i class="ri-list-check-3 me-2 text-primary"></i>Target Assets
                    </h6>
                    @php $done = $job->targetAssets->where('completed', true)->count(); $total = $job->targetAssets->count(); @endphp
                    <span class="badge bg-{{ $done === $total ? 'success' : 'warning' }}-subtle text-{{ $done === $total ? 'success' : 'warning' }}">
                        {{ $done }} / {{ $total }} completed
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Asset</th>
                                    <th>Type</th>
                                    <th>Zone</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($job->targetAssets as $ta)
                                <tr>
                                    <td class="ps-3 fw-medium fs-13">{{ $ta->asset->asset_code }}</td>
                                    <td class="fs-13 text-muted">{{ $assetTypes[$ta->asset->asset_type] ?? $ta->asset->asset_type }}</td>
                                    <td class="fs-13 text-muted">{{ $ta->asset->zone ?? '—' }}</td>
                                    <td>
                                        @if($ta->completed)
                                        <span class="badge bg-success-subtle text-success"><i class="ri-checkbox-circle-line me-1"></i>Done</span>
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

            {{-- Inspection records --}}
            @if($job->inspectionRecords->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="ri-survey-line me-2 text-primary"></i>Inspection Records
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $job->inspectionRecords->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Asset</th>
                                    <th>Date</th>
                                    <th>Result</th>
                                    <th>Doc Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($job->inspectionRecords as $ir)
                                @php $rc = $resultColors[$ir->result] ?? 'secondary'; @endphp
                                <tr>
                                    <td class="ps-3 fw-medium fs-13">{{ $ir->asset->asset_code }}</td>
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
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Installation assets --}}
            @if($job->installationAssets->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="ri-tools-line me-2 text-primary"></i>Installation / Rectification Records
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $job->installationAssets->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Asset</th>
                                    <th>Action</th>
                                    <th>Notes</th>
                                    <th>Recorded</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($job->installationAssets as $ia)
                                <tr>
                                    <td class="ps-3 fw-medium fs-13">{{ $ia->asset->asset_code }}</td>
                                    <td><span class="badge bg-info-subtle text-info">{{ ucfirst($ia->action) }}</span></td>
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

        </div>

        {{-- Right sidebar --}}
        <div class="col-lg-4">

            {{-- Workflow --}}
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
                        <li class="d-flex align-items-center gap-2 fs-13
                            {{ $isCurr ? 'fw-semibold' : ($isPast ? 'text-muted' : 'text-muted opacity-50') }}">
                            @if($isCurr)   <i class="ri-record-circle-line text-{{ $sc2 }}"></i>
                            @elseif($isPast) <i class="ri-checkbox-circle-fill text-success"></i>
                            @else          <i class="ri-circle-line text-muted"></i>
                            @endif
                            {{ $sLabel }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Technicians --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="ri-team-line me-2 text-primary"></i>Technicians</h6>
                </div>
                <div class="card-body">
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
</x-app-layout>
