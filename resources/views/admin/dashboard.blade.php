<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    @push('styles')
    <style>
        .stat-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 22px; }
        .activity-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 5px; }
        .pipeline-bar { height: 10px; border-radius: 6px; }
    </style>
    @endpush

    {{-- Page Title --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Dashboard</h4>
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
                            <p class="text-muted fs-12 text-uppercase fw-semibold mb-1">Total Jobs</p>
                            <h3 class="fw-bold mb-0">{{ $totalJobs }}</h3>
                            <p class="mt-2 mb-0 fs-12">
                                <span class="text-warning fw-medium">{{ $activeJobs }}</span>
                                <span class="text-muted ms-1">active</span>
                                <span class="mx-2 text-muted">·</span>
                                <span class="text-danger fw-medium">{{ $pendingJobs }}</span>
                                <span class="text-muted ms-1">pending review</span>
                            </p>
                        </div>
                        <div class="stat-icon bg-primary-subtle">
                            <i class="ri-briefcase-line text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer pt-0 pb-3 px-3">
                    <a href="{{ route('admin.jobs.index') }}" class="text-primary fs-12">View all jobs <i class="ri-arrow-right-line"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted fs-12 text-uppercase fw-semibold mb-1">Total Assets</p>
                            <h3 class="fw-bold mb-0">{{ $totalAssets }}</h3>
                            <p class="mt-2 mb-0 fs-12">
                                <span class="text-danger fw-medium">{{ $overdueAssets }}</span>
                                <span class="text-muted ms-1">overdue</span>
                                <span class="mx-2 text-muted">·</span>
                                <span class="text-warning fw-medium">{{ $dueSoonAssets }}</span>
                                <span class="text-muted ms-1">due in 30d</span>
                            </p>
                        </div>
                        <div class="stat-icon bg-warning-subtle">
                            <i class="ri-tools-line text-warning"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer pt-0 pb-3 px-3">
                    <a href="{{ route('admin.assets.index') }}" class="text-warning fs-12">View assets <i class="ri-arrow-right-line"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted fs-12 text-uppercase fw-semibold mb-1">Clients</p>
                            <h3 class="fw-bold mb-0">{{ $totalClients }}</h3>
                            <p class="mt-2 mb-0 fs-12">
                                @if($feedbackStats->total > 0)
                                    <span class="text-warning fw-medium">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="ri-star-{{ $i <= round($feedbackStats->avg) ? 'fill' : 'line' }}"></i>
                                        @endfor
                                        {{ $feedbackStats->avg }}
                                    </span>
                                    <span class="text-muted ms-1">avg rating ({{ $feedbackStats->total }})</span>
                                @else
                                    <span class="text-muted">No feedback yet</span>
                                @endif
                            </p>
                        </div>
                        <div class="stat-icon bg-success-subtle">
                            <i class="ri-building-2-line text-success"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer pt-0 pb-3 px-3">
                    <a href="{{ route('admin.master.clients.index') }}" class="text-success fs-12">Manage clients <i class="ri-arrow-right-line"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted fs-12 text-uppercase fw-semibold mb-1">System Users</p>
                            <h3 class="fw-bold mb-0">{{ $totalUsers }}</h3>
                            <p class="mt-2 mb-0 fs-12">
                                <span class="text-info fw-medium">{{ $usersByRole->get('field-technician', 0) }}</span>
                                <span class="text-muted ms-1">technicians</span>
                                <span class="mx-2 text-muted">·</span>
                                <span class="text-secondary fw-medium">{{ $usersByRole->get('client-user', 0) }}</span>
                                <span class="text-muted ms-1">clients</span>
                            </p>
                        </div>
                        <div class="stat-icon bg-info-subtle">
                            <i class="ri-team-line text-info"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer pt-0 pb-3 px-3">
                    <a href="{{ route('admin.users.index') }}" class="text-info fs-12">Manage users <i class="ri-arrow-right-line"></i></a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 2: Job Pipeline + Asset Status + Inspection Alerts ── --}}
    <div class="row g-3 mb-3">

        {{-- Job Pipeline --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-git-branch-line me-2 text-primary"></i>Job Pipeline</h5>
                </div>
                <div class="card-body">
                    @php
                        $pipeline = [
                            'new'                    => ['label' => 'New',                   'color' => 'secondary'],
                            'scheduled'              => ['label' => 'Scheduled',             'color' => 'info'],
                            'in_progress'            => ['label' => 'In Progress',           'color' => 'primary'],
                            'submitted_for_review'   => ['label' => 'Submitted for Review',  'color' => 'warning'],
                            'under_review'           => ['label' => 'Under Review',          'color' => 'warning'],
                            'rectification_required' => ['label' => 'Rectification Needed',  'color' => 'danger'],
                            'approved'               => ['label' => 'Approved',              'color' => 'success'],
                            'issued'                 => ['label' => 'Issued',                'color' => 'success'],
                            'closed'                 => ['label' => 'Closed',               'color' => 'dark'],
                        ];
                        $maxCount = max($jobsByStatus->max(), 1);
                    @endphp
                    @foreach($pipeline as $key => $meta)
                    @php $cnt = $jobsByStatus->get($key, 0); @endphp
                    @if($cnt > 0)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fs-13 text-muted">{{ $meta['label'] }}</span>
                            <span class="badge bg-{{ $meta['color'] }}-subtle text-{{ $meta['color'] }} fs-11">{{ $cnt }}</span>
                        </div>
                        <div class="progress pipeline-bar">
                            <div class="progress-bar bg-{{ $meta['color'] }}"
                                 style="width: {{ round($cnt / $maxCount * 100) }}%"></div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                    @if($jobsByStatus->isEmpty())
                    <p class="text-muted text-center py-3">No jobs yet.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Asset Status Breakdown --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-pie-chart-line me-2 text-warning"></i>Asset Status</h5>
                </div>
                <div class="card-body">
                    @php
                        $assetMeta = [
                            'pass'           => ['label' => 'Pass',           'color' => 'success'],
                            'fail'           => ['label' => 'Fail',           'color' => 'danger'],
                            'under_review'   => ['label' => 'Under Review',  'color' => 'warning'],
                            'not_inspected'  => ['label' => 'Not Inspected', 'color' => 'secondary'],
                            'replaced'       => ['label' => 'Replaced',      'color' => 'dark'],
                        ];
                        $assetTotal = max($assetsByStatus->sum(), 1);
                    @endphp
                    @foreach($assetMeta as $key => $meta)
                    @php $cnt = $assetsByStatus->get($key, 0); $pct = round($cnt / $assetTotal * 100); @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fs-13 text-muted">{{ $meta['label'] }}</span>
                            <span class="fs-12 fw-medium">{{ $cnt }} <span class="text-muted">({{ $pct }}%)</span></span>
                        </div>
                        <div class="progress pipeline-bar">
                            <div class="progress-bar bg-{{ $meta['color'] }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Inspection Alerts + User Distribution --}}
        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold fs-11 mb-3">Inspection Alerts</h6>
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="p-2 rounded bg-danger-subtle">
                                <h4 class="fw-bold text-danger mb-0">{{ $overdueAssets }}</h4>
                                <p class="fs-11 text-muted mb-0 mt-1">Overdue</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded bg-warning-subtle">
                                <h4 class="fw-bold text-warning mb-0">{{ $dueSoonAssets }}</h4>
                                <p class="fs-11 text-muted mb-0 mt-1">Due 30d</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded bg-info-subtle">
                                <h4 class="fw-bold text-info mb-0">{{ $pendingReview }}</h4>
                                <p class="fs-11 text-muted mb-0 mt-1">Pending</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-0">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold fs-11 mb-3">Users by Role</h6>
                    @php
                        $roleColors = [
                            'system-administrator' => 'danger',
                            'manager'              => 'warning',
                            'field-technician'     => 'info',
                            'client-user'          => 'success',
                        ];
                        $roleLabels = [
                            'system-administrator' => 'Admin',
                            'manager'              => 'Manager',
                            'field-technician'     => 'Technician',
                            'client-user'          => 'Client',
                        ];
                    @endphp
                    @foreach($roleLabels as $role => $label)
                    @php $cnt = $usersByRole->get($role, 0); @endphp
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-{{ $roleColors[$role] ?? 'secondary' }}-subtle text-{{ $roleColors[$role] ?? 'secondary' }} fs-11">{{ $label }}</span>
                        </div>
                        <span class="fw-semibold fs-13">{{ $cnt }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- ── Row 3: Recent Jobs + Top Clients ── --}}
    <div class="row g-3 mb-3">

        {{-- Recent Jobs --}}
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0"><i class="ri-briefcase-line me-2 text-primary"></i>Recent Jobs</h5>
                    <a href="{{ route('admin.jobs.index') }}" class="btn btn-sm btn-light">View all</a>
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
                            @forelse($recentJobs as $job)
                            @php
                                $statusColors = [
                                    'new'                    => 'secondary',
                                    'scheduled'              => 'info',
                                    'in_progress'            => 'primary',
                                    'submitted_for_review'   => 'warning',
                                    'under_review'           => 'warning',
                                    'rectification_required' => 'danger',
                                    'approved'               => 'success',
                                    'issued'                 => 'success',
                                    'closed'                 => 'dark',
                                ];
                                $sc = $statusColors[$job->status] ?? 'secondary';
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-medium fs-13">{{ $job->client->name ?? '—' }}</div>
                                    <div class="text-muted fs-12">{{ $job->site->name ?? $job->site->address ?? '—' }}</div>
                                </td>
                                <td class="fs-12">
                                    <span class="badge bg-info-subtle text-info">{{ \App\Models\Job::WORK_TYPES[$job->work_type] ?? $job->work_type }}</span>
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
                                    <a href="{{ route('admin.jobs.show', $job) }}" class="btn btn-sm btn-light py-0 px-2">
                                        <i class="ri-eye-line fs-13"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No jobs yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Top Clients --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0"><i class="ri-building-2-line me-2 text-success"></i>Top Clients</h5>
                    <a href="{{ route('admin.master.clients.index') }}" class="btn btn-sm btn-light">View all</a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($topClients as $idx => $client)
                        <li class="list-group-item px-3 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-xs flex-shrink-0">
                                    <span class="avatar-title rounded-circle bg-{{ ['primary','success','warning','info','secondary'][$idx % 5] }}-subtle
                                                 text-{{ ['primary','success','warning','info','secondary'][$idx % 5] }} fs-13 fw-bold">
                                        {{ strtoupper(substr($client->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-medium fs-13 text-truncate">{{ $client->name }}</div>
                                    <div class="text-muted fs-11">{{ $client->jobs_count }} job{{ $client->jobs_count === 1 ? '' : 's' }}</div>
                                </div>
                                <span class="badge bg-light text-dark border fs-11">#{{ $idx + 1 }}</span>
                            </div>
                        </li>
                        @empty
                        <li class="list-group-item text-center text-muted py-4">No clients yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Row 4: Recent Activity ── --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-history-line me-2 text-info"></i>Recent Activity</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($recentActivity as $activity)
                        @php
                            $logColors = [
                                'master'            => 'primary',
                                'inspection_record' => 'success',
                                'job'               => 'warning',
                                'user'              => 'info',
                                'asset'             => 'danger',
                            ];
                            $lc = $logColors[$activity->log_name] ?? 'secondary';
                        @endphp
                        <li class="list-group-item px-3 py-2">
                            <div class="d-flex align-items-start gap-3">
                                <div class="activity-dot bg-{{ $lc }} mt-1"></div>
                                <div class="flex-grow-1">
                                    <div class="fs-13">
                                        @if($activity->causer)
                                            <span class="fw-medium">{{ $activity->causer->name }}</span>
                                        @else
                                            <span class="fw-medium text-muted">System</span>
                                        @endif
                                        <span class="text-muted ms-1">{{ $activity->description }}</span>
                                        <span class="badge bg-{{ $lc }}-subtle text-{{ $lc }} ms-1 fs-10">{{ ucfirst(str_replace('_', ' ', $activity->log_name)) }}</span>
                                    </div>
                                    <div class="text-muted fs-11 mt-1">{{ $activity->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="list-group-item text-center text-muted py-4">No activity recorded.</li>
                        @endforelse
                    </ul>
                </div>
                @if($recentActivity->count() >= 8)
                <div class="card-footer text-center">
                    <a href="{{ route('admin.audit-log.index') }}" class="text-muted fs-12">View full audit log <i class="ri-arrow-right-line"></i></a>
                </div>
                @endif
            </div>
        </div>
    </div>

</x-app-layout>
