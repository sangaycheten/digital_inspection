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
                    <h4 class="card-title mb-0 flex-grow-1">
                        <i class="ri-award-line me-2 text-primary"></i>My Inspection Certificates
                    </h4>
                </div>
                <div class="card-body">
                    @if(isset($accessibleJobs) && $accessibleJobs->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Certificate No</th>
                                    <th>Site</th>
                                    <th>Work Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="text-end">Download</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accessibleJobs as $certJob)
                                <tr>
                                    <td>
                                        <span class="fw-semibold text-primary">CERT-{{ strtoupper(substr($certJob->id, 0, 8)) }}</span>
                                    </td>
                                    <td>{{ $certJob->site->name ?? $certJob->site->address ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info">
                                            {{ \App\Models\Job::WORK_TYPES[$certJob->work_type] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success">
                                            {{ \App\Models\Job::STATUSES[$certJob->status] }}
                                        </span>
                                    </td>
                                    <td class="text-muted fs-13">{{ $certJob->updated_at->format('d M Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('client.certificates.download', $certJob) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="ri-file-download-line me-1"></i>Download PDF
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-5">
                        <i class="ri-award-line fs-1 text-muted opacity-50"></i>
                        <p class="mt-2 mb-0">No certificates available yet.</p>
                        <small>Certificates will appear here once your inspection has been completed and issued.</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
