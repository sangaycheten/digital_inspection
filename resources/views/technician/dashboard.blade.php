<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <style>
        .stat-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 22px; }
    </style>

    {{-- Page Title --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">My Dashboard</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 1: KPI Cards ── --}}
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-sm-6">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted fs-12 text-uppercase fw-semibold mb-1">Active Jobs</p>
                            <h3 class="fw-bold mb-0">{{ $activeJobsCount }}</h3>
                            <p class="mt-2 mb-0 fs-12 text-muted">Requires your attention</p>
                        </div>
                        <div class="stat-icon bg-primary-subtle">
                            <i class="ri-briefcase-line text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer pt-0 pb-3 px-3">
                    <a href="{{ route('technician.jobs.index') }}" class="text-primary fs-12">View all jobs <i class="ri-arrow-right-line"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted fs-12 text-uppercase fw-semibold mb-1">Pending Review</p>
                            <h3 class="fw-bold mb-0">{{ $pendingReviewCount }}</h3>
                            <p class="mt-2 mb-0 fs-12 text-muted">Awaiting manager approval</p>
                        </div>
                        <div class="stat-icon bg-warning-subtle">
                            <i class="ri-time-line text-warning"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer pt-0 pb-3 px-3">
                    <a href="{{ route('technician.jobs.index', ['status' => 'submitted_for_review']) }}" class="text-warning fs-12">View submitted <i class="ri-arrow-right-line"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted fs-12 text-uppercase fw-semibold mb-1">Completed Jobs</p>
                            <h3 class="fw-bold mb-0">{{ $completedCount }}</h3>
                            <p class="mt-2 mb-0 fs-12 text-muted">Approved or issued</p>
                        </div>
                        <div class="stat-icon bg-success-subtle">
                            <i class="ri-checkbox-circle-line text-success"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer pt-0 pb-3 px-3">
                    <a href="{{ route('technician.jobs.index', ['status' => 'approved']) }}" class="text-success fs-12">View completed <i class="ri-arrow-right-line"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted fs-12 text-uppercase fw-semibold mb-1">Inspections Captured</p>
                            <h3 class="fw-bold mb-0">{{ $inspectionCount }}</h3>
                            <p class="mt-2 mb-0 fs-12 text-muted">Total records submitted</p>
                        </div>
                        <div class="stat-icon bg-info-subtle">
                            <i class="ri-survey-line text-info"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer pt-0 pb-3 px-3">
                    <span class="text-muted fs-12">All time</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 2: Active Jobs + Quick Capture ── --}}
    <div class="row g-3 mb-3">

        {{-- Active Jobs --}}
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0"><i class="ri-briefcase-line me-2 text-primary"></i>My Active Jobs</h5>
                    <a href="{{ route('technician.jobs.index') }}" class="btn btn-sm btn-light">All Jobs</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Client / Site</th>
                                <th>Work Type</th>
                                <th>Status</th>
                                <th>Scheduled</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeJobs as $job)
                            @php
                                $statusColors = [
                                    'new'                    => 'secondary',
                                    'scheduled'              => 'info',
                                    'in_progress'            => 'primary',
                                    'rectification_required' => 'danger',
                                ];
                                $sc = $statusColors[$job->status] ?? 'secondary';
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-medium fs-13">{{ $job->client->name ?? '—' }}</div>
                                    <div class="text-muted fs-12">{{ $job->site->name ?? $job->site->address ?? '—' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info fs-11">
                                        {{ \App\Models\Job::WORK_TYPES[$job->work_type] ?? $job->work_type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} fs-11">
                                        {{ \App\Models\Job::STATUSES[$job->status] ?? $job->status }}
                                    </span>
                                </td>
                                <td class="text-muted fs-12">
                                    {{ $job->scheduled_date ? \Carbon\Carbon::parse($job->scheduled_date)->format('d M Y') : '—' }}
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('technician.jobs.show', $job) }}" class="btn btn-sm btn-light py-0 px-2">
                                        <i class="ri-arrow-right-line fs-13"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="ri-briefcase-line fs-2 opacity-50 d-block mb-2"></i>
                                    No active jobs assigned to you.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Quick Capture + Job Status Breakdown --}}
        <div class="col-xl-4">

            {{-- Quick Capture --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-flashlight-line me-2 text-warning"></i>Quick Capture</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('technician.jobs.index', ['work_type' => 'first_inspection', 'status' => 'in_progress']) }}"
                       class="btn btn-outline-primary text-start d-flex align-items-center justify-content-between">
                        <span><i class="ri-survey-line me-2"></i>First Inspection</span>
                        @if(($workTypeCounts['first_inspection'] ?? 0) > 0)
                            <span class="badge bg-primary rounded-pill">{{ $workTypeCounts['first_inspection'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('technician.jobs.index', ['work_type' => 're_inspection', 'status' => 'in_progress']) }}"
                       class="btn btn-outline-warning text-start d-flex align-items-center justify-content-between">
                        <span><i class="ri-refresh-line me-2"></i>Re-Inspection</span>
                        @if(($workTypeCounts['re_inspection'] ?? 0) > 0)
                            <span class="badge bg-warning rounded-pill">{{ $workTypeCounts['re_inspection'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('technician.jobs.index', ['work_type' => 'installation', 'status' => 'in_progress']) }}"
                       class="btn btn-outline-success text-start d-flex align-items-center justify-content-between">
                        <span><i class="ri-tools-line me-2"></i>Installation</span>
                        @if(($workTypeCounts['installation'] ?? 0) > 0)
                            <span class="badge bg-success rounded-pill">{{ $workTypeCounts['installation'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('technician.jobs.index', ['work_type' => 'rectification', 'status' => 'in_progress']) }}"
                       class="btn btn-outline-danger text-start d-flex align-items-center justify-content-between">
                        <span><i class="ri-hammer-line me-2"></i>Rectification</span>
                        @if(($workTypeCounts['rectification'] ?? 0) > 0)
                            <span class="badge bg-danger rounded-pill">{{ $workTypeCounts['rectification'] }}</span>
                        @endif
                    </a>
                </div>
            </div>

            {{-- My Job Status Breakdown --}}
            <div class="card mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-bar-chart-line me-2 text-info"></i>My Job Summary</h5>
                </div>
                <div class="card-body">
                    @php
                        $pipeline = [
                            'new'                    => ['label' => 'New',                  'color' => 'secondary'],
                            'scheduled'              => ['label' => 'Scheduled',            'color' => 'info'],
                            'in_progress'            => ['label' => 'In Progress',          'color' => 'primary'],
                            'rectification_required' => ['label' => 'Rectification Needed', 'color' => 'danger'],
                            'submitted_for_review'   => ['label' => 'Submitted for Review', 'color' => 'warning'],
                            'approved'               => ['label' => 'Approved',             'color' => 'success'],
                            'issued'                 => ['label' => 'Issued',               'color' => 'success'],
                            'closed'                 => ['label' => 'Closed',               'color' => 'dark'],
                        ];
                        $maxCount = max($jobsByStatus->max() ?: 1, 1);
                    @endphp
                    @foreach($pipeline as $key => $meta)
                    @php $cnt = $jobsByStatus->get($key, 0); @endphp
                    @if($cnt > 0)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fs-12 text-muted">{{ $meta['label'] }}</span>
                            <span class="badge bg-{{ $meta['color'] }}-subtle text-{{ $meta['color'] }} fs-11">{{ $cnt }}</span>
                        </div>
                        <div class="progress" style="height:6px;border-radius:4px">
                            <div class="progress-bar bg-{{ $meta['color'] }}"
                                 style="width:{{ round($cnt / $maxCount * 100) }}%"></div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                    @if($jobsByStatus->isEmpty())
                    <p class="text-muted text-center py-2 mb-0 fs-13">No jobs assigned yet.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- ── Row 3: Today's Schedule + Recent Completed ── --}}
    <div class="row g-3">

        {{-- Today's Schedule --}}
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-calendar-check-line me-2 text-success"></i>
                        Today's Schedule
                        <span class="badge bg-light text-dark border ms-2 fs-11">{{ today()->format('d M Y') }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($todayJobs->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="ri-calendar-line fs-2 opacity-50 d-block mb-2"></i>
                        No jobs scheduled for today.
                    </div>
                    @else
                    <ul class="list-group list-group-flush">
                        @foreach($todayJobs as $job)
                        @php $sc = ['new'=>'secondary','scheduled'=>'info','in_progress'=>'primary','rectification_required'=>'danger'][$job->status] ?? 'secondary'; @endphp
                        <li class="list-group-item px-3 py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-medium fs-13">{{ $job->client->name ?? '—' }}</div>
                                    <div class="text-muted fs-12">{{ $job->site->name ?? $job->site->address ?? '—' }}</div>
                                    <div class="mt-1">
                                        <span class="badge bg-info-subtle text-info fs-10 me-1">{{ \App\Models\Job::WORK_TYPES[$job->work_type] ?? $job->work_type }}</span>
                                        <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} fs-10">{{ \App\Models\Job::STATUSES[$job->status] ?? $job->status }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('technician.jobs.show', $job) }}" class="btn btn-sm btn-primary">
                                    <i class="ri-arrow-right-line"></i>
                                </a>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recently Completed --}}
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-checkbox-circle-line me-2 text-success"></i>Recently Completed</h5>
                </div>
                <div class="card-body p-0">
                    @if($recentCompleted->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="ri-checkbox-circle-line fs-2 opacity-50 d-block mb-2"></i>
                        No completed jobs yet.
                    </div>
                    @else
                    <ul class="list-group list-group-flush">
                        @foreach($recentCompleted as $job)
                        @php
                            $sc = ['submitted_for_review'=>'warning','under_review'=>'warning','approved'=>'success','issued'=>'success','closed'=>'dark'][$job->status] ?? 'secondary';
                        @endphp
                        <li class="list-group-item px-3 py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-medium fs-13">{{ $job->client->name ?? '—' }}</div>
                                    <div class="text-muted fs-12">{{ $job->site->name ?? $job->site->address ?? '—' }}</div>
                                    <div class="mt-1">
                                        <span class="badge bg-info-subtle text-info fs-10 me-1">{{ \App\Models\Job::WORK_TYPES[$job->work_type] ?? $job->work_type }}</span>
                                        <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} fs-10">{{ \App\Models\Job::STATUSES[$job->status] ?? $job->status }}</span>
                                    </div>
                                </div>
                                <div class="text-muted fs-11 text-end">
                                    {{ $job->updated_at->format('d M Y') }}
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
        </div>

    </div>

</x-app-layout>
