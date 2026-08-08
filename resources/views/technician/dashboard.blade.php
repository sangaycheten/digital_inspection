<x-app-layout>
    @php $title = 'Dashboard'; @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Technician Dashboard</h4>
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
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">My Active Jobs</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">0</h4>
                            <a href="#" class="text-decoration-underline">View my jobs</a>
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

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Inspections Today</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">0</h4>
                            <a href="#" class="text-decoration-underline">View inspections</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle rounded fs-3">
                                <i class="ri-survey-line text-success"></i>
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
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Completed This Week</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">0</h4>
                            <a href="#" class="text-decoration-underline">View history</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-warning-subtle rounded fs-3">
                                <i class="ri-checkbox-circle-line text-warning"></i>
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
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">My Documents</p>
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
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h4 class="card-title mb-0 flex-grow-1">Today's Schedule</h4>
                    <a href="#" class="btn btn-sm btn-primary">
                        <i class="ri-add-line me-1"></i> New Inspection
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Job #</th>
                                    <th>Site</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No jobs scheduled for today.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Quick Capture</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Select the type of capture to begin:</p>
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary text-start">
                            <i class="ri-survey-line me-2"></i> First Inspection
                        </a>
                        <a href="#" class="btn btn-outline-warning text-start">
                            <i class="ri-refresh-line me-2"></i> Re-Inspection
                        </a>
                        <a href="#" class="btn btn-outline-success text-start">
                            <i class="ri-tools-line me-2"></i> Installation / Rectification
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
