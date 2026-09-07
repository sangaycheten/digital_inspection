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
    $jobStatusColors = [
        'submitted_for_review'   => 'warning',
        'under_review'           => 'primary',
        'approved'               => 'success',
        'rectification_required' => 'danger',
    ];
    $isDrillDown = isset($job);
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">
                    @if($isDrillDown)
                    <a href="{{ route('reviewer.inspections.index', request()->except('job_id', 'page')) }}"
                       class="text-muted me-2"><i class="ri-arrow-left-line"></i></a>
                    {{ $job->client->name ?? '—' }} — {{ \App\Models\Job::WORK_TYPES[$job->work_type] }}
                    @else
                    Review Inspections
                    @endif
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('reviewer.dashboard') }}">Home</a></li>
                        @if($isDrillDown)
                        <li class="breadcrumb-item"><a href="{{ route('reviewer.inspections.index') }}">Inspections</a></li>
                        <li class="breadcrumb-item active">Review</li>
                        @else
                        <li class="breadcrumb-item active">Inspections</li>
                        @endif
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

    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    {{-- JOB LIST (default view) --}}
    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    @if(!$isDrillDown)

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
        <div class="card-header d-flex align-items-center gap-2">
            <h5 class="card-title mb-0 flex-grow-1">
                <i class="ri-briefcase-line me-2 text-primary"></i>
                @if(request('status') === 'approved')
                    Approved Jobs
                @elseif(request('status') === 'submitted')
                    Jobs with Pending Records
                @else
                    Jobs Pending Review
                @endif
                <span class="badge bg-primary-subtle text-primary ms-1">{{ $jobs->total() }}</span>
            </h5>
        </div>

        <div class="card-body border-bottom pb-3">
            <form method="GET" action="{{ route('reviewer.inspections.index') }}" class="row g-2 align-items-end">
                @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
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
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i>Filter</button>
                    <a href="{{ route('reviewer.inspections.index', request('status') ? ['status' => request('status')] : []) }}"
                       class="btn btn-light btn-sm ms-1"><i class="ri-refresh-line"></i> Reset</a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Client / Job</th>
                            <th>Site</th>
                            <th>Technician(s)</th>
                            <th class="text-center">Records</th>
                            <th>Job Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobs as $job)
                        @php
                            $total     = $job->inspection_records_count;
                            $submitted = $job->submitted_count;
                            $approved  = $job->approved_count;
                            $draft     = $job->draft_count;
                            $jsc = $jobStatusColors[$job->status] ?? 'secondary';
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium fs-13">{{ $job->client->name ?? '—' }}</div>
                                <div class="d-flex align-items-center gap-1 mt-1">
                                    <span class="badge bg-info-subtle text-info fs-11">
                                        {{ \App\Models\Job::WORK_TYPES[$job->work_type] }}
                                    </span>
                                    @if($job->scheduled_date)
                                    <span class="text-muted fs-11">{{ $job->scheduled_date->format('d M Y') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="fs-13 text-muted">{{ $job->site->name ?? $job->site->address ?? '—' }}</td>
                            <td>
                                @foreach($job->technicians as $tech)
                                <span class="badge bg-light text-dark border fs-11 me-1">{{ $tech->name }}</span>
                                @endforeach
                            </td>
                            <td class="text-center">
                                <span class="fs-15 fw-semibold">{{ $total }}</span>
                                <div class="d-flex justify-content-center gap-1 mt-1 flex-wrap">
                                    @if($submitted)
                                    <span class="badge bg-warning-subtle text-warning fs-11">{{ $submitted }} pending</span>
                                    @endif
                                    @if($approved)
                                    <span class="badge bg-success-subtle text-success fs-11">{{ $approved }} approved</span>
                                    @endif
                                    @if($draft)
                                    <span class="badge bg-danger-subtle text-danger fs-11">{{ $draft }} sent back</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $jsc }}-subtle text-{{ $jsc }}">
                                    {{ \App\Models\Job::STATUSES[$job->status] }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('reviewer.inspections.index', array_merge(request()->query(), ['job_id' => $job->id])) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="ri-eye-line me-1"></i>Review Records
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="ri-briefcase-line fs-24 d-block mb-2"></i>No jobs found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($jobs->hasPages())
        <div class="card-footer">{{ $jobs->links() }}</div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    {{-- WIZARD DRILL-DOWN --}}
    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    @else

    @php
        $stepCount     = $recordsByType->count() + 1; // type steps + summary
        $grandTotal    = $allRecords->count();
        $grandApproved = $allRecords->where('document_status', 'approved')->count();
        $grandPending  = $allRecords->where('document_status', 'submitted')->count();
        $grandSentBack = $allRecords->filter(fn($r) => $r->document_status === 'draft' && str_starts_with($r->required_action ?? '', '[REJECTED]'))->count();
        $allApproved   = $grandTotal > 0 && $grandApproved >= $grandTotal;
    @endphp

    {{-- Job summary strip --}}
    <div class="alert alert-primary alert-border-left mb-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div>
                <span class="fw-semibold">{{ $job->client->name ?? '—' }}</span>
                <span class="text-muted mx-1">·</span>
                {{ $job->site->name ?? $job->site->address }}
            </div>
            <span class="badge bg-info-subtle text-info">{{ \App\Models\Job::WORK_TYPES[$job->work_type] }}</span>
            @if($job->buildings->isNotEmpty())
            <span class="text-muted fs-12">Buildings:
                @foreach($job->buildings as $b)
                <span class="badge bg-light text-dark border me-1">{{ $b->name_or_level }}</span>
                @endforeach
            </span>
            @endif
            @php $jsc3 = $jobStatusColors[$job->status] ?? 'secondary'; @endphp
            <span class="badge bg-{{ $jsc3 }}-subtle text-{{ $jsc3 }} ms-auto">
                {{ \App\Models\Job::STATUSES[$job->status] }}
            </span>
        </div>
    </div>

    @if($recordsByType->isEmpty())
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="ri-survey-line fs-24 d-block mb-2"></i>No inspection records for this job yet.
        </div>
    </div>
    @else

    <div class="row g-3">

        {{-- ── Left: Step nav ─────────────────────────────────────────────── --}}
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body p-2">

                    @foreach($recordsByType as $assetType => $typeRecords)
                    @php
                        $stepIdx      = $loop->index;
                        $typePending  = $typeRecords->where('document_status', 'submitted')->count();
                        $typeApproved = $typeRecords->where('document_status', 'approved')->count();
                        $typeSentBack = $typeRecords->filter(fn($r) => $r->document_status === 'draft' && str_starts_with($r->required_action ?? '', '[REJECTED]'))->count();
                        $stepResults  = $typeRecords->groupBy('result')->map->count();
                    @endphp
                    <div class="wizard-nav-item d-flex align-items-center gap-2 p-2 rounded mb-1"
                         data-step="{{ $stepIdx }}" role="button" onclick="goToStep({{ $stepIdx }})">
                        <div class="step-circle" data-step="{{ $stepIdx }}">
                            <span class="step-num">{{ $loop->iteration }}</span>
                            <i class="ri-check-line step-check d-none"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fs-13 fw-medium text-truncate">
                                {{ $assetTypes[$assetType] ?? $assetType }}
                            </div>
                            <div class="fs-11 text-muted">
                                {{ $typeApproved }} / {{ $typeRecords->count() }} approved
                            </div>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @if($typePending)
                                <span class="badge bg-warning-subtle text-warning" style="font-size:10px">
                                    {{ $typePending }} pending
                                </span>
                                @endif
                                @if($typeSentBack)
                                <span class="badge bg-danger-subtle text-danger" style="font-size:10px">
                                    {{ $typeSentBack }} sent back
                                </span>
                                @endif
                                @foreach($stepResults as $res => $cnt)
                                @php $rc = $resultColors[$res] ?? 'secondary'; @endphp
                                <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }}" style="font-size:10px">
                                    {{ ucwords(str_replace('_', ' ', $res)) }} {{ $cnt }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach

                    {{-- Summary step --}}
                    <div class="wizard-nav-item d-flex align-items-center gap-2 p-2 rounded mb-1"
                         data-step="{{ $recordsByType->count() }}" role="button" onclick="goToStep({{ $recordsByType->count() }})">
                        <div class="step-circle" data-step="{{ $recordsByType->count() }}">
                            <i class="ri-flag-2-line" style="font-size:14px"></i>
                        </div>
                        <div class="fs-13 fw-medium">Summary</div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Center: Step panels ─────────────────────────────────────────── --}}
        <div class="col-lg-6">

            @foreach($recordsByType as $assetType => $typeRecords)
            @php
                $stepIdx     = $loop->index;
                $questions   = $questionsByType->get($assetType, collect());
                $typeApproved = $typeRecords->where('document_status', 'approved')->count();
            @endphp

            <div class="wizard-panel" data-step="{{ $stepIdx }}"
                 data-total="{{ $typeRecords->count() }}"
                 data-approved="{{ $typeApproved }}"
                 @if(!$loop->first) style="display:none" @endif>

                <div class="card mb-0">
                    <div class="card-header d-flex align-items-center">
                        <h6 class="card-title mb-0 flex-grow-1">
                            <i class="ri-tag-3-line me-2 text-primary"></i>
                            {{ $assetTypes[$assetType] ?? $assetType }}
                            <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $typeRecords->count() }}</span>
                        </h6>
                        <span class="badge bg-success-subtle text-success fs-11">
                            {{ $typeApproved }} / {{ $typeRecords->count() }} approved
                        </span>
                    </div>

                    <div class="card-body p-0">
                        @foreach($typeRecords as $record)
                        @php
                            $isApproved = $record->document_status === 'approved';
                            $isRejected = $record->document_status === 'draft' && str_starts_with($record->required_action ?? '', '[REJECTED]');
                            $rc         = $resultColors[$record->result] ?? 'secondary';
                            $answers    = $record->answers->keyBy('questionnaire_id');
                            $collapseId = 'rec_' . $record->id;
                            $rowBg      = $isApproved ? 'bg-success-subtle' : ($isRejected ? 'bg-danger-subtle' : '');
                        @endphp

                        <div class="border-bottom {{ $rowBg }}">

                            {{-- Asset header row --}}
                            <div class="d-flex align-items-center px-3 py-2 gap-2">
                                <div class="flex-grow-1">
                                    <span class="fw-medium fs-13">{{ $record->asset->asset_code }}</span>
                                    @if($record->asset->building)
                                    <span class="text-muted fs-12 ms-2">{{ $record->asset->building->name_or_level }}</span>
                                    @endif
                                    @if($record->asset->zone)
                                    <span class="text-muted fs-12 ms-1">· {{ $record->asset->zone }}</span>
                                    @endif
                                    <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }} ms-2 fs-11">
                                        {{ ucwords(str_replace('_', ' ', $record->result)) }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted fs-11">{{ $record->inspection_date->format('d M Y') }}</span>
                                    @if($isApproved)
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="ri-shield-check-line me-1"></i>Approved
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    @elseif($isRejected)
                                    <span class="badge bg-danger-subtle text-danger">
                                        <i class="ri-arrow-go-back-line me-1"></i>Sent Back
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}">
                                        <i class="ri-eye-line me-1"></i>View
                                    </button>
                                    @else
                                    <span class="badge bg-warning-subtle text-warning">
                                        <i class="ri-time-line me-1"></i>Pending
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}">
                                        <i class="ri-eye-line me-1"></i>Review
                                    </button>
                                    @endif
                                    <a href="{{ route('reviewer.inspections.show', $record) }}"
                                       class="btn btn-sm btn-light" title="Assessment Trail">
                                        <i class="ri-history-line"></i>
                                    </a>
                                </div>
                            </div>

                            {{-- Collapsible detail + actions --}}
                            <div class="collapse {{ $isApproved ? '' : 'show' }}" id="{{ $collapseId }}">
                                <div class="px-3 pb-3 pt-1 bg-light bg-opacity-50">

                                    {{-- Rejection note banner --}}
                                    @if($isRejected)
                                    <div class="alert alert-danger py-2 px-3 mt-2 mb-2 fs-13">
                                        <i class="ri-arrow-go-back-line me-1"></i>
                                        <strong>Sent back:</strong>
                                        {{ ltrim(str_replace('[REJECTED]', '', $record->required_action)) }}
                                    </div>
                                    @endif

                                    {{-- Technician info --}}
                                    <div class="d-flex gap-3 mt-2 mb-2 fs-12 text-muted">
                                        <span><i class="ri-user-line me-1"></i>{{ $record->technician->name ?? '—' }}</span>
                                        <span><i class="ri-calendar-line me-1"></i>{{ $record->inspection_date->format('d M Y') }}</span>
                                    </div>

                                    {{-- Inspection fields --}}
                                    @foreach([
                                        'Condition'          => $record->condition,
                                        'Defect Description' => $record->defect_description,
                                        'Reason for Result'  => $record->reason_for_result,
                                        'Recommendation'     => $record->recommendation,
                                        'Required Action'    => (!$isRejected ? $record->required_action : null),
                                    ] as $label => $value)
                                    @if($value)
                                    <div class="mb-2">
                                        <p class="fs-11 text-uppercase text-muted fw-semibold mb-1">{{ $label }}</p>
                                        <p class="fs-13 mb-0">{{ $value }}</p>
                                    </div>
                                    @endif
                                    @endforeach

                                    {{-- Checklist answers --}}
                                    @if($questions->isNotEmpty() && $answers->isNotEmpty())
                                    <hr class="my-2">
                                    <p class="fs-11 text-uppercase text-muted fw-semibold mb-2">
                                        <i class="ri-list-check-3 me-1"></i>Inspection Checklist
                                    </p>
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($questions as $q)
                                        @php
                                            $qVal = $answers->get($q->id)?->answer_value ?? null;
                                            $sqLetterCounters = [];
                                        @endphp
                                        @if($qVal !== null)
                                        <div class="border rounded p-2 bg-white">
                                            <p class="fs-12 fw-medium mb-1 d-flex align-items-center gap-1">
                                                <span class="badge bg-secondary-subtle text-secondary fw-semibold" style="min-width:20px">{{ $loop->iteration }}</span>
                                                {{ $q->name }}
                                            </p>
                                            <p class="fs-13 mb-0 ps-1">{{ $qVal }}</p>

                                            @foreach($q->subQuestionnaires as $sq)
                                            @php
                                                $sqVal    = $answers->get($sq->id)?->answer_value ?? null;
                                                $sqCondKey = $sq->condition ?? '__none__';
                                                $sqLetterCounters[$sqCondKey] = $sqLetterCounters[$sqCondKey] ?? 0;
                                                $sqLetter = chr(ord('a') + $sqLetterCounters[$sqCondKey]++);
                                            @endphp
                                            @if($sqVal !== null)
                                            <div class="mt-1 ps-2 border-start border-2 border-secondary-subtle">
                                                <p class="fs-11 text-muted mb-0 d-flex align-items-center gap-1">
                                                    <span class="badge bg-light text-secondary border fw-semibold" style="min-width:18px;font-size:10px">{{ $sqLetter }}</span>
                                                    {{ $sq->name }}
                                                </p>
                                                <p class="fs-12 mb-0 ps-1">{{ $sqVal }}</p>
                                            </div>
                                            @endif
                                            @endforeach
                                        </div>
                                        @endif
                                        @endforeach
                                    </div>
                                    @endif

                                    {{-- Previous inspection comparison --}}
                                    @if($record->previousInspection)
                                    @php $prevRc = $resultColors[$record->previousInspection->result] ?? 'secondary'; @endphp
                                    <div class="mt-2 p-2 border rounded bg-white fs-12">
                                        <p class="text-muted fw-semibold mb-1 fs-11">
                                            <i class="ri-history-line me-1"></i>Previous Inspection
                                            ({{ $record->previousInspection->inspection_date->format('d M Y') }})
                                        </p>
                                        <span class="badge bg-{{ $prevRc }}-subtle text-{{ $prevRc }}">
                                            {{ ucwords(str_replace('_', ' ', $record->previousInspection->result)) }}
                                        </span>
                                        @if($record->previousInspection->condition)
                                        <span class="ms-2 text-muted">{{ $record->previousInspection->condition }}</span>
                                        @endif
                                    </div>
                                    @endif

                                    {{-- Approve / Send Back actions (only for submitted records, not already-sent-back) --}}
                                    @if(!$isApproved && !$isRejected)
                                    <hr class="my-2">
                                    <div class="d-flex gap-2 align-items-start flex-wrap">
                                        {{-- Approve --}}
                                        <form method="POST" action="{{ route('reviewer.inspections.approve', $record) }}"
                                              class="approve-form">
                                            @csrf
                                            <button type="button" class="btn btn-success btn-sm approve-btn"
                                                    data-asset="{{ $record->asset->asset_code }}"
                                                    data-result="{{ ucwords(str_replace('_', ' ', $record->result)) }}">
                                                <i class="ri-checkbox-circle-line me-1"></i>Approve
                                            </button>
                                        </form>

                                        {{-- Send Back --}}
                                        <div class="flex-grow-1">
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#rejectForm_{{ $record->id }}">
                                                <i class="ri-arrow-go-back-line me-1"></i>Send Back
                                            </button>
                                            <div class="collapse mt-2" id="rejectForm_{{ $record->id }}">
                                                <form method="POST" action="{{ route('reviewer.inspections.reject', $record) }}">
                                                    @csrf
                                                    <textarea name="rejection_note" class="form-control form-control-sm mb-2"
                                                              rows="2" placeholder="Reason / what to fix…" required></textarea>
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="ri-send-plane-line me-1"></i>Confirm Send Back
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Summary panel --}}
            <div class="wizard-panel" data-step="{{ $recordsByType->count() }}" style="display:none">
                <div class="card mb-0">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ri-flag-2-line me-2 text-primary"></i>Review Summary
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm fs-13 mb-3">
                            <thead class="table-light">
                                <tr>
                                    <th>Asset Type</th>
                                    <th class="text-center">Approved</th>
                                    <th class="text-center">Sent Back</th>
                                    <th class="text-center">Pending</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recordsByType as $assetType => $typeRecords)
                                @php
                                    $tApproved  = $typeRecords->where('document_status', 'approved')->count();
                                    $tPending   = $typeRecords->where('document_status', 'submitted')->count();
                                    $tSentBack  = $typeRecords->filter(fn($r) => $r->document_status === 'draft' && str_starts_with($r->required_action ?? '', '[REJECTED]'))->count();
                                    $tTotal     = $typeRecords->count();
                                    $rowClass   = $tApproved === $tTotal ? 'table-success' : ($tSentBack > 0 ? 'table-danger' : '');
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>{{ $assetTypes[$assetType] ?? $assetType }}</td>
                                    <td class="text-center fw-medium text-success">{{ $tApproved }}</td>
                                    <td class="text-center fw-medium {{ $tSentBack ? 'text-danger' : 'text-muted' }}">{{ $tSentBack }}</td>
                                    <td class="text-center {{ $tPending ? 'text-warning fw-medium' : 'text-muted' }}">{{ $tPending }}</td>
                                    <td class="text-center text-muted">{{ $tTotal }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-semibold">
                                <tr>
                                    <td>Total</td>
                                    <td class="text-center text-success">{{ $grandApproved }}</td>
                                    <td class="text-center {{ $grandSentBack ? 'text-danger' : 'text-muted' }}">{{ $grandSentBack }}</td>
                                    <td class="text-center {{ $grandPending ? 'text-warning' : 'text-muted' }}">{{ $grandPending }}</td>
                                    <td class="text-center">{{ $grandTotal }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        @if($allApproved)
                        <div class="alert alert-success d-flex align-items-center gap-2 mb-0">
                            <i class="ri-shield-check-line fs-18"></i>
                            All inspection records have been approved.
                        </div>
                        @else
                        <div class="d-flex flex-column gap-2">
                            @if($grandPending)
                            <div class="alert alert-warning d-flex align-items-center gap-2 mb-0">
                                <i class="ri-time-line fs-18"></i>
                                <span><strong>{{ $grandPending }}</strong> record(s) still pending review.</span>
                            </div>
                            @endif
                            @if($grandSentBack)
                            <div class="alert alert-danger d-flex align-items-center gap-2 mb-0">
                                <i class="ri-arrow-go-back-line fs-18"></i>
                                <span><strong>{{ $grandSentBack }}</strong> record(s) sent back to technician for revision.</span>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Navigation buttons --}}
            <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
                <button type="button" class="btn btn-light" id="prevBtn" disabled onclick="changeStep(-1)">
                    <i class="ri-arrow-left-line me-1"></i>Back
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">
                        Next <i class="ri-arrow-right-line ms-1"></i>
                    </button>
                    <a href="{{ route('reviewer.inspections.index') }}" class="btn btn-light">
                        <i class="ri-arrow-left-line me-1"></i>Back to Jobs
                    </a>
                </div>
            </div>

        </div>

        {{-- ── Right: Progress panel ─────────────────────────────────────────── --}}
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="ri-bar-chart-line me-2 text-primary"></i>Approval Progress
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($recordsByType as $assetType => $typeRecords)
                    @php
                        $stepIdx  = $loop->index;
                        $tApproved = $typeRecords->where('document_status', 'approved')->count();
                        $tTotal    = $typeRecords->count();
                        $pct       = $tTotal ? round($tApproved / $tTotal * 100) : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center fs-12 mb-1">
                            <span class="text-muted text-truncate me-2" style="max-width:120px">
                                {{ $assetTypes[$assetType] ?? $assetType }}
                            </span>
                            <span class="fw-medium text-nowrap">{{ $tApproved }} / {{ $tTotal }}</span>
                        </div>
                        <div class="progress" style="height:5px">
                            <div class="progress-bar bg-success" style="width:{{ $pct }}%" role="progressbar"></div>
                        </div>
                    </div>
                    @endforeach

                    <hr class="my-2">
                    <div class="d-flex justify-content-between fs-13">
                        <span class="text-muted">Total</span>
                        <span class="fw-semibold">{{ $grandApproved }} / {{ $grandTotal }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @endif
    @endif

    @push('styles')
    <style>
    .step-circle {
        width: 30px; height: 30px; min-width: 30px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 600;
        border: 2px solid #dee2e6;
        color: #6c757d;
        background: transparent;
        transition: background .2s, border-color .2s, color .2s;
    }
    .step-circle.step-active { background: #405189; border-color: #405189; color: #fff; }
    .step-circle.step-done   { background: #0ab39c; border-color: #0ab39c; color: #fff; }

    .wizard-nav-item { cursor: pointer; transition: background .15s; }
    .wizard-nav-item:hover  { background: #f3f6f9; }
    .wizard-nav-item.wiz-active { background: #e8eaf3; }
    .wizard-nav-item.wiz-active .fs-13 { color: #405189; }
    </style>
    @endpush

    @if($isDrillDown)
    @push('scripts')
    <script>
    // ── Approve confirmation ─────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.approve-btn');
        if (!btn) return;

        const form   = btn.closest('.approve-form');
        const asset  = btn.dataset.asset;
        const result = btn.dataset.result;

        Swal.fire({
            title: 'Approve Inspection?',
            html: `<p class="mb-1">Asset: <strong>${asset}</strong></p>
                   <p class="mb-0">Result: <strong>${result}</strong></p>
                   <p class="text-muted mt-2 mb-0" style="font-size:13px">Asset status will be updated immediately.</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="ri-checkbox-circle-line me-1"></i>Yes, Approve',
            cancelButtonText:  'Cancel',
            confirmButtonColor: '#0ab39c',
            cancelButtonColor:  '#6c757d',
            reverseButtons: true,
            focusCancel: true,
        }).then(r => {
            if (r.isConfirmed) form.submit();
        });
    });

    // ── Wizard ───────────────────────────────────────────────────────────────
    let currentStep = 0;
    const totalSteps = {{ $stepCount }};

    function isStepComplete(stepIdx) {
        const panel = document.querySelector(`.wizard-panel[data-step="${stepIdx}"]`);
        if (!panel || !panel.dataset.total) return false;
        return parseInt(panel.dataset.approved) >= parseInt(panel.dataset.total);
    }

    function goToStep(n) {
        document.querySelectorAll('.wizard-panel').forEach(p => p.style.display = 'none');
        const panel = document.querySelector(`.wizard-panel[data-step="${n}"]`);
        if (panel) panel.style.display = '';

        document.querySelectorAll('.wizard-nav-item').forEach(item => {
            item.classList.toggle('wiz-active', parseInt(item.dataset.step) === n);
        });

        document.querySelectorAll('.step-circle[data-step]').forEach(el => {
            const s    = parseInt(el.dataset.step);
            const done = isStepComplete(s);
            el.classList.toggle('step-done',   done);
            el.classList.toggle('step-active', s === n && !done);
            const numEl   = el.querySelector('.step-num');
            const checkEl = el.querySelector('.step-check');
            if (numEl)   numEl.classList.toggle('d-none', done);
            if (checkEl) checkEl.classList.toggle('d-none', !done);
        });

        currentStep = n;
        document.getElementById('prevBtn').disabled = (currentStep === 0);
        const isLast = currentStep === totalSteps - 1;
        document.getElementById('nextBtn').classList.toggle('d-none', isLast);
    }

    function changeStep(delta) {
        goToStep(Math.min(Math.max(currentStep + delta, 0), totalSteps - 1));
    }

    document.addEventListener('DOMContentLoaded', () => goToStep(0));
    </script>
    @endpush
    @endif

</x-app-layout>
