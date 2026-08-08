<x-app-layout>
    @php $title = 'Overview'; @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Client Overview</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Overview</li>
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
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">My Sites</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">0</h4>
                            <a href="#" class="text-decoration-underline">View sites</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                <i class="ri-building-line text-primary"></i>
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
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Total Assets</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">0</h4>
                            <a href="#" class="text-decoration-underline">View assets</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle rounded fs-3">
                                <i class="ri-tools-line text-success"></i>
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
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Issued Documents</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">0</h4>
                            <a href="#" class="text-decoration-underline">View documents</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-warning-subtle rounded fs-3">
                                <i class="ri-file-list-3-line text-warning"></i>
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
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-1">Recent Inspections</p>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">0</h4>
                            <a href="#" class="text-decoration-underline">View history</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle rounded fs-3">
                                <i class="ri-survey-line text-info"></i>
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
                <div class="card-header d-flex align-items-center">
                    <h4 class="card-title mb-0 flex-grow-1">Recent Inspection Reports</h4>
                    <a href="#" class="btn btn-sm btn-outline-secondary">
                        <i class="ri-download-2-line me-1"></i> Export
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Report #</th>
                                    <th>Site</th>
                                    <th>Asset</th>
                                    <th>Inspection Date</th>
                                    <th>Status</th>
                                    <th>Document</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No reports available yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
