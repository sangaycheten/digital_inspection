<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Welcome, {{ auth()->user()->name }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase fw-medium text-muted fs-12 mb-1">My Sites</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ $sitesCount }}</h4>
                        </div>
                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                            <i class="ri-building-line text-primary"></i>
                        </span>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('client.sites.index') }}" class="text-decoration-underline fs-13">View sites</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase fw-medium text-muted fs-12 mb-1">Total Assets</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ $assetsCount }}</h4>
                        </div>
                        <span class="avatar-title bg-success-subtle rounded fs-3">
                            <i class="ri-tools-line text-success"></i>
                        </span>
                    </div>
                    <div class="mt-3">
                        @php
                            $pass = $assetsByStatus['pass'] ?? 0;
                            $fail = $assetsByStatus['fail'] ?? 0;
                        @endphp
                        @if($pass || $fail)
                        <span class="badge bg-success-subtle text-success me-1">{{ $pass }} pass</span>
                        <span class="badge bg-danger-subtle text-danger">{{ $fail }} fail</span>
                        @endif
                        <a href="{{ route('client.assets.index') }}" class="text-decoration-underline fs-13 ms-1">View assets</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase fw-medium text-muted fs-12 mb-1">Issued Reports</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ $issuedJobsCount }}</h4>
                        </div>
                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                            <i class="ri-file-list-3-line text-warning"></i>
                        </span>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('client.reports.index') }}" class="text-decoration-underline fs-13">View reports</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase fw-medium text-muted fs-12 mb-1">Due Within 90 Days</p>
                            <h4 class="fs-22 fw-semibold mb-0">{{ $dueAssets->count() }}</h4>
                        </div>
                        <span class="avatar-title bg-danger-subtle rounded fs-3">
                            <i class="ri-calendar-check-line text-danger"></i>
                        </span>
                    </div>
                    <div class="mt-3">
                        <span class="fs-12 text-muted">Assets approaching inspection due date</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Compliance Summary --}}
    @if($totalAssets > 0)
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-semibold fs-14">
                    <i class="ri-shield-check-line me-2 text-primary"></i>Compliance Overview
                </span>
                @if($compliance !== null)
                <span class="fw-bold fs-16 {{ $compliance >= 80 ? 'text-success' : ($compliance >= 50 ? 'text-warning' : 'text-danger') }}">
                    {{ $compliance }}% compliant
                </span>
                @endif
            </div>
            <div class="progress" style="height:10px;">
                @if($totalAssets > 0)
                <div class="progress-bar bg-success" style="width:{{ round($passCount/$totalAssets*100) }}%"
                     title="{{ $passCount }} Pass"></div>
                <div class="progress-bar bg-danger" style="width:{{ round($failCount/$totalAssets*100) }}%"
                     title="{{ $failCount }} Fail"></div>
                <div class="progress-bar bg-secondary opacity-50" style="width:{{ round($otherCount/$totalAssets*100) }}%"
                     title="{{ $otherCount }} Other"></div>
                @endif
            </div>
            <div class="d-flex gap-3 mt-2 fs-12 text-muted">
                <span><i class="ri-checkbox-blank-circle-fill text-success me-1"></i>{{ $passCount }} Pass</span>
                <span><i class="ri-checkbox-blank-circle-fill text-danger me-1"></i>{{ $failCount }} Fail</span>
                <span><i class="ri-checkbox-blank-circle-fill text-secondary me-1"></i>{{ $otherCount }} Other</span>
                <span class="ms-auto">{{ $totalAssets }} total assets</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Upcoming Inspections --}}
    @if($upcomingJobs->isNotEmpty())
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="ri-calendar-event-line me-2 text-info"></i>Upcoming Inspections
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Site</th>
                            <th>Work Type</th>
                            <th>Scheduled Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcomingJobs as $job)
                        @php
                            $sc = $job->status === 'in_progress' ? 'primary' : 'info';
                        @endphp
                        <tr>
                            <td class="ps-3 fs-13">{{ $job->site->name ?? $job->site->address ?? '—' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border fs-11">
                                    {{ \App\Models\Job::WORK_TYPES[$job->work_type] }}
                                </span>
                            </td>
                            <td class="fs-13 text-muted">
                                @if($job->scheduled_date)
                                    {{ $job->scheduled_date->format('d M Y') }}
                                    @if($job->scheduled_time)
                                        <span class="ms-1">{{ \Carbon\Carbon::parse($job->scheduled_time)->format('H:i') }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">TBC</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} fs-11">
                                    {{ \App\Models\Job::STATUSES[$job->status] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="row g-3">

        {{-- Recent jobs --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-briefcase-line me-2 text-primary"></i>Recent Inspection Jobs
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($recentJobs->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="ri-briefcase-line fs-2 opacity-50 d-block mb-2"></i>No jobs yet.
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Site</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentJobs as $job)
                                @php
                                    $sc = match($job->status) {
                                        'issued', 'closed'         => 'success',
                                        'approved'                 => 'success',
                                        'submitted_for_review',
                                        'under_review'             => 'warning',
                                        default                    => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="ps-3 fs-13">{{ $job->site->name ?? $job->site->address ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info fs-11">
                                            {{ \App\Models\Job::WORK_TYPES[$job->work_type] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} fs-11">
                                            {{ \App\Models\Job::STATUSES[$job->status] }}
                                        </span>
                                    </td>
                                    <td class="text-muted fs-12">{{ $job->updated_at->format('d M Y') }}</td>
                                    <td>
                                        @if(in_array($job->status, ['issued', 'closed']) && $job->certificate_accessible)
                                        <a href="{{ route('client.certificates.download', $job) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="ri-file-download-line"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
                @if($recentJobs->isNotEmpty())
                <div class="card-footer text-end">
                    <a href="{{ route('client.reports.index') }}" class="btn btn-sm btn-light">View all reports</a>
                </div>
                @endif
            </div>
        </div>

        {{-- Assets due soon --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-calendar-check-line me-2 text-danger"></i>Assets Due for Inspection
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($dueAssets->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="ri-calendar-check-line fs-2 opacity-50 d-block mb-2"></i>No assets due within 90 days.
                    </div>
                    @else
                    <ul class="list-group list-group-flush">
                        @foreach($dueAssets as $asset)
                        @php
                            $daysLeft = now()->diffInDays($asset->next_inspection_due_date, false);
                            $color = $daysLeft <= 0 ? 'danger' : ($daysLeft <= 30 ? 'warning' : 'info');
                        @endphp
                        <li class="list-group-item px-3 py-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fw-medium fs-13">{{ $asset->asset_code }}</span>
                                    <span class="text-muted fs-12 ms-2">{{ $asset->site->name ?? $asset->site->address ?? '' }}</span>
                                </div>
                                <span class="badge bg-{{ $color }}-subtle text-{{ $color }} fs-11">
                                    @if($daysLeft <= 0)
                                        Overdue
                                    @else
                                        {{ $daysLeft }}d left
                                    @endif
                                </span>
                            </div>
                            <div class="text-muted fs-11 mt-1">
                                Due: {{ $asset->next_inspection_due_date->format('d M Y') }}
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                @if($dueAssets->isNotEmpty())
                <div class="card-footer text-end">
                    <a href="{{ route('client.assets.index') }}" class="btn btn-sm btn-light">View all assets</a>
                </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
