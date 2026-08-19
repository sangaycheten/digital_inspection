<x-app-layout>
    @php $title = 'Dashboard'; @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Reviewer Dashboard</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Pending Review</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $pendingCount }}</h4>
                            <a href="{{ route('reviewer.inspections.index') }}" class="text-decoration-underline">View inspections</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-warning-subtle rounded fs-3">
                                <i class="ri-survey-line text-warning"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Approved Today</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $approvedToday }}</h4>
                            <a href="{{ route('reviewer.inspections.index', ['status' => 'approved']) }}" class="text-decoration-underline">View approved</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle rounded fs-3">
                                <i class="ri-checkbox-circle-line text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Pending Documents</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">0</h4>
                            <a href="#" class="text-decoration-underline">View documents</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle rounded fs-3">
                                <i class="ri-file-list-3-line text-info"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Open Jobs</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $openJobs }}</h4>
                            <a href="{{ route('admin.jobs.index') }}" class="text-decoration-underline">View jobs</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                <i class="ri-briefcase-line text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Inspections Awaiting Review</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Job #</th>
                                    <th>Site</th>
                                    <th>Technician</th>
                                    <th>Submitted</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No inspections pending review.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
