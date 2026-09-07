<x-app-layout>
    <x-slot name="title">Inspections</x-slot>

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
    $isDrillDown = isset($job);
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">
                    @if($isDrillDown)
                    <a href="{{ route('admin.inspections.index', request()->except('job_id', 'page')) }}" class="text-muted me-2">
                        <i class="ri-arrow-left-line"></i>
                    </a>
                    Inspections — {{ \App\Models\Job::WORK_TYPES[$job->work_type] }}
                    @else
                    Inspections
                    @endif
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        @if($isDrillDown)
                        <li class="breadcrumb-item"><a href="{{ route('admin.inspections.index') }}">Inspections</a></li>
                        <li class="breadcrumb-item active">{{ $job->client->name ?? '—' }}</li>
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

    {{-- Stats --}}
    <div class="row g-3 mb-3">
        {{-- Total --}}
        <div class="col-md-3">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase fw-medium text-muted fs-12 mb-1">Total Jobs</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ $totalJobs }}</h4>
                            <p class="text-muted fs-12 mb-0 mt-1">{{ $totalRecords }} inspection record{{ $totalRecords !== 1 ? 's' : '' }}</p>
                        </div>
                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                            <i class="ri-briefcase-line text-primary"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending --}}
        <div class="col-md-3">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase fw-medium text-muted fs-12 mb-1">Pending Review</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ $pendingCount }}</h4>
                            <p class="text-muted fs-12 mb-0 mt-1">
                                record{{ $pendingCount !== 1 ? 's' : '' }} across
                                <strong>{{ $pendingJobs }}</strong> job{{ $pendingJobs !== 1 ? 's' : '' }}
                            </p>
                        </div>
                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                            <i class="ri-time-line text-warning"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sent back --}}
        <div class="col-md-3">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase fw-medium text-muted fs-12 mb-1">Sent Back</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ $sentBackCount }}</h4>
                            <p class="text-muted fs-12 mb-0 mt-1">
                                record{{ $sentBackCount !== 1 ? 's' : '' }} across
                                <strong>{{ $sentBackJobs }}</strong> job{{ $sentBackJobs !== 1 ? 's' : '' }}
                            </p>
                        </div>
                        <span class="avatar-title bg-danger-subtle rounded fs-3">
                            <i class="ri-arrow-go-back-line text-danger"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Approved --}}
        <div class="col-md-3">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase fw-medium text-muted fs-12 mb-1">Approved</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ $approvedTotal }}</h4>
                            <p class="text-muted fs-12 mb-0 mt-1">
                                <strong class="text-success">{{ $approvedToday }}</strong> approved today
                            </p>
                        </div>
                        <span class="avatar-title bg-success-subtle rounded fs-3">
                            <i class="ri-shield-check-line text-success"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
            <h5 class="card-title mb-0 flex-grow-1">
                @if($isDrillDown)
                <i class="ri-survey-line me-2 text-primary"></i>Records for Job
                <span class="badge bg-info-subtle text-info ms-1">{{ \App\Models\Job::WORK_TYPES[$job->work_type] }}</span>
                <span class="badge bg-primary-subtle text-primary ms-1">{{ $records->total() }}</span>
                @else
                <i class="ri-briefcase-line me-2 text-primary"></i>Jobs with Inspection Records
                <span class="badge bg-primary-subtle text-primary ms-1">{{ $jobs->total() }}</span>
                @endif
            </h5>
            @if($isDrillDown)
            <a href="{{ route('admin.inspections.index', request()->except('job_id', 'page')) }}"
               class="btn btn-sm btn-light">
                <i class="ri-arrow-left-line me-1"></i>Back to Jobs
            </a>
            @endif
        </div>

        {{-- Filters --}}
        <div class="card-body border-bottom pb-3">
            <form method="GET" action="{{ route('admin.inspections.index') }}" class="row g-2 align-items-end">
                @if($isDrillDown)
                <input type="hidden" name="job_id" value="{{ $job->id }}">
                @endif
                <div class="col-md-2">
                    <label class="form-label text-muted fs-12 mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Pending Review</option>
                        <option value="approved"  {{ request('status') === 'approved'  ? 'selected' : '' }}>Approved</option>
                    </select>
                </div>
                @if(!$isDrillDown)
                <div class="col-md-2">
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
                <div class="col-md-2">
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
                <div class="col-md-2">
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
                    <label class="form-label text-muted fs-12 mb-1">Reviewer</label>
                    <select name="reviewer_id" class="form-select form-select-sm">
                        <option value="">All Reviewers</option>
                        @foreach($managers as $mgr)
                        <option value="{{ $mgr->id }}" {{ request('reviewer_id') == $mgr->id ? 'selected' : '' }}>
                            {{ $mgr->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @else
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
                @endif
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="ri-search-line me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.inspections.index', $isDrillDown ? ['job_id' => $job->id] : []) }}"
                       class="btn btn-light btn-sm ms-1">
                        <i class="ri-refresh-line"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">

            {{-- ── Job-level grouped view ───────────────────────────────────────── --}}
            @if(!$isDrillDown)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Job / Client</th>
                            <th>Site</th>
                            <th>Reviewer</th>
                            <th>Technician(s)</th>
                            <th class="text-center">Records</th>
                            <th>Inspection Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobs as $job)
                        @php
                        $total     = $job->inspection_records_count;
                        $draft     = $job->draft_count;
                        $submitted = $job->submitted_count;
                        $approved  = $job->approved_count;

                        // Compute inspection status from record breakdown (not job.status which can drift)
                        if ($approved === $total && $total > 0) {
                            $inspStatus = ['label' => 'Fully Approved',   'color' => 'success', 'icon' => 'ri-shield-check-line'];
                        } elseif ($submitted > 0 && $draft === 0) {
                            $inspStatus = ['label' => 'Pending Review',   'color' => 'primary', 'icon' => 'ri-time-line'];
                        } elseif ($submitted > 0 && $draft > 0) {
                            $inspStatus = ['label' => 'Partially Submitted', 'color' => 'warning', 'icon' => 'ri-send-plane-line'];
                        } elseif ($approved > 0 && $submitted === 0 && $draft > 0) {
                            $inspStatus = ['label' => 'Needs Rework',     'color' => 'danger',  'icon' => 'ri-arrow-go-back-line'];
                        } else {
                            $inspStatus = ['label' => 'In Progress',      'color' => 'secondary','icon' => 'ri-edit-line'];
                        }
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
                            <td class="fs-13">
                                @if($job->client?->manager)
                                <span class="badge bg-info-subtle text-info">
                                    <i class="ri-user-star-line me-1"></i>{{ $job->client->manager->name }}
                                </span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @foreach($job->technicians as $tech)
                                <span class="badge bg-light text-dark border fs-11 me-1">{{ $tech->name }}</span>
                                @endforeach
                            </td>
                            <td class="text-center">
                                <span class="fs-15 fw-semibold">{{ $total }}</span>
                                <div class="d-flex justify-content-center gap-1 mt-1 flex-wrap">
                                    @if($draft)
                                    <span class="badge bg-warning-subtle text-warning fs-11" title="Draft">
                                        {{ $draft }}D
                                    </span>
                                    @endif
                                    @if($submitted)
                                    <span class="badge bg-primary-subtle text-primary fs-11" title="Pending Review">
                                        {{ $submitted }}P
                                    </span>
                                    @endif
                                    @if($approved)
                                    <span class="badge bg-success-subtle text-success fs-11" title="Approved">
                                        {{ $approved }}A
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $inspStatus['color'] }}-subtle text-{{ $inspStatus['color'] }} fs-12 px-2 py-1">
                                    <i class="{{ $inspStatus['icon'] }} me-1"></i>{{ $inspStatus['label'] }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.inspections.index', array_merge(request()->query(), ['job_id' => $job->id])) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="ri-eye-line me-1"></i>View Records
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="ri-briefcase-line fs-24 d-block mb-2"></i>No jobs with inspection records found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ── Drill-down: individual records for one job ────────────────────── --}}
            @else

            {{-- Job summary bar --}}
            <div class="px-3 py-2 bg-light border-bottom d-flex align-items-center gap-3 flex-wrap">
                <div>
                    <span class="text-muted fs-12">Client</span>
                    <span class="fw-medium fs-13 ms-1">{{ $job->client->name ?? '—' }}</span>
                </div>
                <div class="vr"></div>
                <div>
                    <span class="text-muted fs-12">Site</span>
                    <span class="fs-13 ms-1">{{ $job->site->name ?? $job->site->address ?? '—' }}</span>
                </div>
                <div class="vr"></div>
                <div>
                    <span class="text-muted fs-12">Technician(s)</span>
                    @foreach($job->technicians as $tech)
                    <span class="badge bg-light text-dark border ms-1 fs-11">{{ $tech->name }}</span>
                    @endforeach
                </div>
                <div class="vr"></div>
                <div>
                    <span class="text-muted fs-12">Reviewer</span>
                    @if($job->client?->manager)
                    <span class="badge bg-info-subtle text-info ms-1 fs-11">
                        <i class="ri-user-star-line me-1"></i>{{ $job->client->manager->name }}
                    </span>
                    @else
                    <span class="ms-1 text-muted fs-12">Not assigned</span>
                    @endif
                </div>
                <div class="vr"></div>
                <div>
                    @php $jsc2 = $jobStatusColors[$job->status] ?? 'secondary'; @endphp
                    <span class="badge bg-{{ $jsc2 }}-subtle text-{{ $jsc2 }}">
                        {{ \App\Models\Job::STATUSES[$job->status] }}
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Asset</th>
                            <th>Building</th>
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
                            <td class="fs-13 text-muted">{{ $record->asset->building?->name_or_level ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }}">
                                    {{ ucwords(str_replace('_', ' ', $record->result)) }}
                                </span>
                            </td>
                            <td class="fs-13">{{ $record->technician->name ?? '—' }}</td>
                            <td class="fs-13 text-muted">{{ $record->inspection_date->format('d M Y') }}</td>
                            <td>
                                @if($record->document_status === 'approved')
                                <span class="badge bg-success-subtle text-success">
                                    <i class="ri-shield-check-line me-1"></i>Approved
                                </span>
                                @elseif($record->document_status === 'submitted')
                                <span class="badge bg-primary-subtle text-primary">
                                    <i class="ri-send-plane-line me-1"></i>Under Review
                                </span>
                                @elseif(str_starts_with($record->required_action ?? '', '[REJECTED]'))
                                <span class="badge bg-danger-subtle text-danger">
                                    <i class="ri-arrow-go-back-line me-1"></i>Sent Back
                                </span>
                                @else
                                <span class="badge bg-warning-subtle text-warning">
                                    <i class="ri-save-3-line me-1"></i>Draft
                                </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.inspections.show', $record) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="ri-eye-line me-1"></i>View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ri-survey-line fs-24 d-block mb-2"></i>No records match the current filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif

        </div>

        @if(!$isDrillDown && $jobs->hasPages())
        <div class="card-footer">{{ $jobs->links() }}</div>
        @elseif($isDrillDown && $records->hasPages())
        <div class="card-footer">{{ $records->links() }}</div>
        @endif
    </div>

</x-app-layout>
