<x-app-layout>
    <x-slot name="title">Jobs</x-slot>

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
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Jobs</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Jobs</li>
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-briefcase-line me-2 text-primary"></i>All Jobs
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $jobs->total() }}</span>
                    </h5>
                    <a href="{{ route('admin.jobs.create') }}" class="btn btn-sm btn-primary">
                        <i class="ri-add-line me-1"></i> Schedule Job
                    </a>
                </div>

                <div class="card-body border-bottom pb-3">
                    <form method="GET" action="{{ route('admin.jobs.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label text-muted fs-12 mb-1">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Scope notes…" value="{{ request('search') }}">
                        </div>
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
                            <label class="form-label text-muted fs-12 mb-1">Work Type</label>
                            <select name="work_type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                @foreach(\App\Models\Job::WORK_TYPES as $val => $label)
                                <option value="{{ $val }}" {{ request('work_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                @foreach(\App\Models\Job::STATUSES as $val => $label)
                                <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i> Filter</button>
                            <a href="{{ route('admin.jobs.index') }}" class="btn btn-light btn-sm ms-1"><i class="ri-refresh-line"></i> Reset</a>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Client / Site</th>
                                    <th>Work Type</th>
                                    <th>Status</th>
                                    <th>Scheduled</th>
                                    <th>Technicians</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jobs as $job)
                                @php $sc = $statusColors[$job->status] ?? 'secondary'; @endphp
                                <tr>
                                    <td class="ps-3 text-muted fs-12">{{ $jobs->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class="fw-medium fs-13">{{ $job->client->name ?? '—' }}</div>
                                        <div class="text-muted fs-12">{{ $job->site->name ?? $job->site->address }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info">
                                            {{ \App\Models\Job::WORK_TYPES[$job->work_type] ?? $job->work_type }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }}">
                                            {{ \App\Models\Job::STATUSES[$job->status] ?? $job->status }}
                                        </span>
                                    </td>
                                    <td class="fs-13 text-muted">
                                        {{ $job->scheduled_date?->format('d M Y') ?? '—' }}
                                    </td>
                                    <td>
                                        @foreach($job->technicians->take(3) as $tech)
                                        <span class="badge bg-light text-dark border me-1 fs-11">{{ $tech->name }}</span>
                                        @endforeach
                                        @if($job->technicians->count() > 3)
                                        <span class="text-muted fs-11">+{{ $job->technicians->count() - 3 }} more</span>
                                        @endif
                                    </td>
                                    <td class="text-muted fs-12">{{ $job->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="hstack gap-1">
                                            <a href="{{ route('admin.jobs.show', $job) }}"
                                               class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            @if(!$job->isClosed())
                                            <a href="{{ route('admin.jobs.edit', $job) }}"
                                               class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
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
        </div>
    </div>

</x-app-layout>
