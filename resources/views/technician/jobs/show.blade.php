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
    $scheduledDateOk   = !$job->scheduled_date || today()->gte($job->scheduled_date);
    $canCapture        = !$job->isClosed() && in_array($job->status, ['scheduled', 'in_progress', 'rectification_required']) && $scheduledDateOk;
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
    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible alert-border-left fade show" role="alert">
        <i class="ri-alert-line me-3 align-middle fs-16"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @php
    $rejectedRecords = $job->inspectionRecords->filter(fn($ir) =>
        $ir->document_status === 'draft' && str_starts_with($ir->required_action ?? '', '[REJECTED]')
    );
    @endphp
    @if($rejectedRecords->isNotEmpty())
    <div class="alert alert-danger alert-border-left">
        <i class="ri-arrow-go-back-line me-2 fs-16 align-middle"></i>
        <strong>{{ $rejectedRecords->count() }} inspection record(s) were sent back for revision.</strong>
        Please open the inspection form, correct the flagged items, then re-submit.
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
            @if(!$job->isClosed() && in_array($job->status, ['scheduled', 'in_progress', 'rectification_required']) && !$scheduledDateOk)
            <div class="card mb-3 border-warning">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="flex-shrink-0">
                        <i class="ri-calendar-event-line text-warning fs-28"></i>
                    </div>
                    <div>
                        <div class="fw-semibold fs-14">Inspection Not Yet Open</div>
                        <div class="text-muted fs-13">
                            This job is scheduled to start on
                            <strong>{{ $job->scheduled_date->format('d M Y') }}</strong>.
                            You can begin capturing from that date onwards.
                        </div>
                    </div>
                </div>
            </div>
            @elseif($canCapture)
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="ri-camera-line me-2 text-primary"></i>Field Capture
                    </h6>
                </div>
                <div class="card-body">

                    @if($isInspectionJob || $job->work_type === 'combined')
                    @php
                        $draftCnt     = $inspectionSummary->get('draft', 0);
                        $submittedCnt = $inspectionSummary->get('submitted', 0);
                        $approvedCnt  = $inspectionSummary->get('approved', 0);
                        $sentBackCnt  = $job->inspectionRecords->filter(fn($ir) =>
                            $ir->document_status === 'draft' && str_starts_with($ir->required_action ?? '', '[REJECTED]')
                        )->count();
                        $pureDraftCnt = $draftCnt - $sentBackCnt;
                        $lockedCnt    = $submittedCnt + $approvedCnt;
                        $pct          = $totalAssets > 0 ? round($lockedCnt / $totalAssets * 100) : 0;

                        if ($lockedCnt >= $totalAssets && $totalAssets > 0) {
                            $btnLabel = 'View Submitted Records';
                            $btnIcon  = 'ri-eye-line';
                            $btnClass = 'btn-outline-success';
                        } elseif ($inspectedCount > 0) {
                            $btnLabel = 'Continue Inspection';
                            $btnIcon  = 'ri-edit-line';
                            $btnClass = 'btn-warning';
                        } else {
                            $btnLabel = 'Start Inspection';
                            $btnIcon  = 'ri-survey-line';
                            $btnClass = 'btn-primary';
                        }
                    @endphp
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0 bg-primary-subtle rounded p-2" style="line-height:1">
                            <i class="ri-survey-line text-primary fs-20"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold fs-14">Inspection Recording</div>
                            <div class="text-muted fs-12 mb-2">
                                Open each asset, answer the checklist, and record a pass/fail result. Submit when done.
                            </div>

                            {{-- Progress bar --}}
                            @if($totalAssets > 0)
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="progress flex-grow-1" style="height:6px">
                                    <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="fs-12 text-muted text-nowrap">{{ $lockedCnt }} / {{ $totalAssets }}</span>
                            </div>
                            @if($draftCnt || $submittedCnt || $approvedCnt)
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @if($sentBackCnt)
                                <span class="badge bg-danger-subtle text-danger fs-11">
                                    <i class="ri-arrow-go-back-line me-1"></i>Sent Back {{ $sentBackCnt }}
                                </span>
                                @endif
                                @if($pureDraftCnt > 0)
                                <span class="badge bg-warning-subtle text-warning fs-11">
                                    <i class="ri-save-3-line me-1"></i>Draft {{ $pureDraftCnt }}
                                </span>
                                @endif
                                @if($submittedCnt)
                                <span class="badge bg-primary-subtle text-primary fs-11">
                                    <i class="ri-send-plane-line me-1"></i>Submitted {{ $submittedCnt }}
                                </span>
                                @endif
                                @if($approvedCnt)
                                <span class="badge bg-success-subtle text-success fs-11">
                                    <i class="ri-shield-check-line me-1"></i>Approved {{ $approvedCnt }}
                                </span>
                                @endif
                            </div>
                            @endif
                            @endif

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="{{ route('technician.jobs.inspect', $job) }}"
                                   class="btn btn-sm {{ $btnClass }}">
                                    <i class="{{ $btnIcon }} me-1"></i>{{ $btnLabel }}
                                </a>

                                @if($draftCnt > 0)
                                <form id="submit-review-form" method="POST" action="{{ route('technician.jobs.submitForReview', $job) }}">
                                    @csrf
                                    <button type="button" id="submit-review-btn" class="btn btn-sm btn-success"
                                            data-count="{{ $draftCnt }}">
                                        <i class="ri-send-plane-line me-1"></i>Submit for Review
                                    </button>
                                </form>
                                @endif
                            </div>
                            @error('submit')
                            <div class="text-danger fs-12 mt-1"><i class="ri-error-warning-line me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @endif

                    @if(($isInstallationJob || $job->work_type === 'combined') && ($isInspectionJob || $job->work_type === 'combined'))
                    <hr class="my-3">
                    @endif

                    @if($isInstallationJob || $job->work_type === 'combined')
                    <hr class="my-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0 bg-warning-subtle rounded p-2" style="line-height:1">
                            <i class="ri-tools-line text-warning fs-20"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold fs-14">Installation / Rectification</div>
                            <div class="text-muted fs-12 mb-2">
                                Record any new asset installations, replacements, or rectification work carried out on site.
                            </div>
                            <a href="{{ route('technician.jobs.install', $job) }}"
                               class="btn btn-sm btn-outline-warning">
                                <i class="ri-tools-line me-1"></i>Record Installation
                            </a>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
            @endif

            {{-- Register New Asset --}}
            @if(!$job->isClosed())
            <div class="card mb-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="flex-shrink-0 bg-secondary-subtle rounded p-2" style="line-height:1">
                        <i class="ri-add-box-line text-secondary fs-20"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold fs-14">Register New Asset</div>
                        <div class="text-muted fs-12">
                            @if($scheduledDateOk)
                                Found an asset on site not yet in the register? Add it here.
                            @else
                                Available from <strong>{{ $job->scheduled_date->format('d M Y') }}</strong>.
                            @endif
                        </div>
                    </div>
                    @if($scheduledDateOk)
                    <button type="button" class="btn btn-sm btn-outline-secondary flex-shrink-0"
                            data-bs-toggle="modal" data-bs-target="#addAssetModal">
                        <i class="ri-add-line me-1"></i>Add Asset
                    </button>
                    @else
                    <button type="button" class="btn btn-sm btn-outline-secondary flex-shrink-0" disabled>
                        <i class="ri-calendar-event-line me-1"></i>Not Yet Available
                    </button>
                    @endif
                </div>
            </div>
            @endif

            {{-- Registered assets for this site --}}
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center">
                    <h6 class="card-title mb-0 flex-grow-1">
                        <i class="ri-stack-line me-2 text-primary"></i>Site Asset Register
                    </h6>
                    <span class="badge bg-primary-subtle text-primary">{{ $siteAssets->count() }} asset(s)</span>
                </div>
                @if($siteAssets->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Asset Code</th>
                                <th>Type</th>
                                <th>Building</th>
                                <th>Zone</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $currentType = null; @endphp
                            @foreach($siteAssets as $asset)
                            @if($asset->asset_type !== $currentType)
                            @php $currentType = $asset->asset_type; @endphp
                            <tr class="table-light">
                                <td colspan="5" class="ps-3 py-1">
                                    <span class="fw-semibold fs-11 text-uppercase text-primary">
                                        {{ $assetTypes[$currentType] ?? $currentType }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td class="ps-3 fw-semibold fs-13">{{ $asset->asset_code }}</td>
                                <td class="text-muted fs-12">{{ $assetTypes[$asset->asset_type] ?? $asset->asset_type }}</td>
                                <td class="text-muted fs-12">{{ $asset->building->name_or_level ?? '—' }}</td>
                                <td class="text-muted fs-12">{{ $asset->zone ?? '—' }}</td>
                                <td>
                                    @php
                                        $sc = match($asset->current_status) {
                                            'pass'           => 'success',
                                            'fail'           => 'danger',
                                            'under_review'   => 'warning',
                                            'restricted_use' => 'warning',
                                            default          => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} text-capitalize">
                                        {{ str_replace('_', ' ', $asset->current_status ?? 'not inspected') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="card-body text-center text-muted py-4">
                    <i class="ri-stack-line fs-2 opacity-50"></i>
                    <p class="mt-1 mb-0 fs-13">No assets registered for this site yet.</p>
                </div>
                @endif
            </div>

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
                                    <th>Status</th>
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
                                        <span class="badge bg-success-subtle text-success"><i class="ri-shield-check-line me-1"></i>Approved</span>
                                        @elseif($ir->document_status === 'submitted')
                                        <span class="badge bg-primary-subtle text-primary"><i class="ri-send-plane-line me-1"></i>Under Review</span>
                                        @elseif(str_starts_with($ir->required_action ?? '', '[REJECTED]'))
                                        <span class="badge bg-danger-subtle text-danger"><i class="ri-arrow-go-back-line me-1"></i>Sent Back</span>
                                        @else
                                        <span class="badge bg-warning-subtle text-warning"><i class="ri-save-3-line me-1"></i>Draft</span>
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

{{-- Add Asset Modal --}}
<div class="modal fade" id="addAssetModal" tabindex="-1" aria-labelledby="addAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('technician.jobs.assets.store', $job) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addAssetModalLabel">
                        <i class="ri-add-box-line me-2 text-primary"></i>Register New Asset
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border fs-12 mb-3 py-2">
                        <i class="ri-map-pin-line me-1"></i>
                        <strong>Site:</strong> {{ $job->site->name ?? $job->site->address }}
                        @if($job->client) &nbsp;·&nbsp; <strong>Client:</strong> {{ $job->client->name }} @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium fs-13">Asset Code <span class="text-danger">*</span></label>
                        <input type="text" name="asset_code"
                               class="form-control @error('asset_code') is-invalid @enderror"
                               value="{{ old('asset_code') }}"
                               placeholder="e.g. AP01, FE-B2-01"
                               required autofocus>
                        @error('asset_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium fs-13">Asset Type <span class="text-danger">*</span></label>
                        <select name="asset_type" class="form-select @error('asset_type') is-invalid @enderror" required>
                            <option value="">— Select type —</option>
                            @foreach($assetTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('asset_type') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                        @error('asset_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    @if($job->buildings->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label fw-medium fs-13">Building</label>
                        <select name="building_id" class="form-select @error('building_id') is-invalid @enderror" required>
                            <option value="" disabled {{ old('building_id') ? '' : 'selected' }}>— Select Building —</option>
                            @foreach($job->buildings as $b)
                            <option value="{{ $b->id }}" {{ old('building_id') == $b->id ? 'selected' : (($job->buildings->count() === 1) ? 'selected' : '') }}>
                                {{ $b->name_or_level }}
                            </option>
                            @endforeach
                        </select>
                        @error('building_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-medium fs-13">Zone / Location</label>
                        <input type="text" name="zone"
                               class="form-control @error('zone') is-invalid @enderror"
                               value="{{ old('zone') }}"
                               placeholder="e.g. Level 2, Stairwell A">
                        @error('zone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium fs-13">Group ID</label>
                        <input type="text" name="group_id"
                               class="form-control @error('group_id') is-invalid @enderror"
                               value="{{ old('group_id') }}" placeholder="Optional — links assets from same batch">
                        @error('group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr class="my-2">
                    <p class="fw-medium fs-13 mb-2 text-muted">Equipment Details</p>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-medium fs-13">Make</label>
                            <input type="text" name="make"
                                   class="form-control form-control-sm @error('make') is-invalid @enderror"
                                   value="{{ old('make') }}" placeholder="Manufacturer">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium fs-13">Model</label>
                            <input type="text" name="model"
                                   class="form-control form-control-sm @error('model') is-invalid @enderror"
                                   value="{{ old('model') }}" placeholder="Model no.">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-medium fs-13">Serial / Batch No.</label>
                            <input type="text" name="serial_or_batch"
                                   class="form-control form-control-sm @error('serial_or_batch') is-invalid @enderror"
                                   value="{{ old('serial_or_batch') }}" placeholder="Serial or batch">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium fs-13">Rating</label>
                            <input type="text" name="rating"
                                   class="form-control form-control-sm @error('rating') is-invalid @enderror"
                                   value="{{ old('rating') }}" placeholder="e.g. 12kN">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <label class="form-label fw-medium fs-13">Fixing Type</label>
                            <input type="text" name="fixing_type"
                                   class="form-control form-control-sm @error('fixing_type') is-invalid @enderror"
                                   value="{{ old('fixing_type') }}" placeholder="e.g. Through-bolt">
                        </div>
                    </div>

                    <hr class="my-2">
                    <p class="fw-medium fs-13 mb-2 text-muted">Dates</p>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-medium fs-13">Install Date</label>
                            <input type="date" name="install_date"
                                   class="form-control form-control-sm @error('install_date') is-invalid @enderror"
                                   value="{{ old('install_date') }}">
                            @error('install_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium fs-13">Next Inspection Due</label>
                            <input type="date" name="next_inspection_due_date"
                                   class="form-control form-control-sm @error('next_inspection_due_date') is-invalid @enderror"
                                   value="{{ old('next_inspection_due_date') }}">
                            @error('next_inspection_due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i>Add Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Re-open modal if validation failed (errors present)
    @if($errors->hasAny(['asset_code', 'asset_type', 'building_id', 'zone', 'group_id', 'serial_or_batch', 'rating', 'fixing_type', 'install_date', 'next_inspection_due_date']))
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('addAssetModal')).show();
    });
    @endif

    const submitBtn = document.getElementById('submit-review-btn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function () {
            const count = this.dataset.count;
            Swal.fire({
                title: 'Submit for Review?',
                html: `<p class="mb-0">You are about to submit <strong>${count}</strong> draft record(s) to the reviewer.</p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-send-plane-line me-1"></i>Yes, Submit',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#0ab39c',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('submit-review-form').submit();
                }
            });
        });
    }
</script>
@endpush
</x-app-layout>
