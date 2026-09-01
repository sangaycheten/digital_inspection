<x-app-layout>
    <x-slot name="title">Job Details</x-slot>

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
    $resultColors = [
        'pass'           => 'success',
        'fail'           => 'danger',
        'under_review'   => 'warning',
        'restricted_use' => 'warning',
        'not_inspected'  => 'secondary',
        'not_located'    => 'dark',
    ];
    $sc = $statusColors[$job->status] ?? 'secondary';
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">
                    Job — <span class="text-primary">{{ \App\Models\Job::WORK_TYPES[$job->work_type] }}</span>
                    <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} ms-2 fs-12">
                        {{ \App\Models\Job::STATUSES[$job->status] }}
                    </span>
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.jobs.index') }}">Jobs</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible alert-border-left fade show" role="alert">
        <i class="ri-checkbox-circle-line me-3 align-middle fs-16"></i>{{ session('success') }}
        @if(session('certificate_ready'))
        &nbsp;
        <a href="{{ route('admin.jobs.certificate', $job) }}" class="btn btn-sm btn-success ms-2">
            <i class="ri-file-download-line me-1"></i>Download Certificate
        </a>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(in_array($job->status, ['issued', 'closed']))
    <div class="alert alert-info alert-border-left d-flex align-items-center justify-content-between">
        <span><i class="ri-award-line me-2 fs-16 align-middle"></i>Inspection certificate is available for this job.</span>
        <a href="{{ route('admin.jobs.certificate', $job) }}" class="btn btn-sm btn-primary ms-3">
            <i class="ri-file-download-line me-1"></i>Download Certificate
        </a>
    </div>
    @endif

    <div class="row g-3">

        {{-- Left: tabs --}}
        <div class="col-lg-8">

            <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-details" role="tab">
                        <i class="ri-briefcase-line me-1"></i> Job Details
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-assets" role="tab">
                        <i class="ri-tools-line me-1"></i> Assets
                        <span class="badge bg-primary-subtle text-primary ms-1">
                            {{ $job->targetAssets->count() + $job->installationAssets->count() + $job->inspectionRecords->count() }}
                        </span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                {{-- Details Tab --}}
                <div class="tab-pane fade show active" id="tab-details">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-3">Location</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted ps-0 fs-13" style="width:120px">Client</td>
                                            <td class="fw-medium fs-13">{{ $job->client->name ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Site</td>
                                            <td class="fs-13">{{ $job->site->name ?? $job->site->address }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Buildings</td>
                                            <td class="fs-13">
                                                @forelse($job->buildings as $b)
                                                <span class="badge bg-light text-dark border me-1">{{ $b->name_or_level }}</span>
                                                @empty
                                                <span class="text-muted">—</span>
                                                @endforelse
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-3">Schedule</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted ps-0 fs-13" style="width:120px">Work Type</td>
                                            <td class="fs-13">
                                                <span class="badge bg-info-subtle text-info">
                                                    {{ \App\Models\Job::WORK_TYPES[$job->work_type] }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Scheduled</td>
                                            <td class="fs-13">
                                                @if($job->scheduled_date)
                                                    {{ $job->scheduled_date->format('d M Y') }}
                                                    @if($job->scheduled_time)
                                                        {{ \Carbon\Carbon::parse($job->scheduled_time)->format('H:i') }}
                                                    @endif
                                                    <span class="text-muted ms-1">{{ \Carbon\Carbon::now($job->site->timezone)->format('T') }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Created</td>
                                            <td class="fs-13">{{ site_time($job->created_at, $job->site->timezone) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Created by</td>
                                            <td class="fs-13">{{ $job->creator->name ?? '—' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                @if($job->scope_notes)
                                <div class="col-12">
                                    <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-2">Scope Notes</h6>
                                    <p class="fs-13 mb-0">{{ $job->scope_notes }}</p>
                                </div>
                                @endif

                                {{-- Technician-Building Assignment Matrix --}}
                                <div class="col-12">
                                    <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-2">Technician-Building Assignments</h6>
                                    @if($assignments->isEmpty())
                                    <span class="text-muted fs-13">No assignments.</span>
                                    @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm align-middle mb-0" style="max-width:480px">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-2 fs-12">Building</th>
                                                    <th class="fs-12">Technicians</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($assignments as $building => $rows)
                                                <tr>
                                                    <td class="ps-2 fw-medium fs-13">{{ $building }}</td>
                                                    <td>
                                                        @foreach($rows as $row)
                                                        <span class="badge bg-primary-subtle text-primary me-1 fs-12">
                                                            <i class="ri-user-line me-1"></i>{{ $row->technician }}
                                                        </span>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Assets Tab --}}
                <div class="tab-pane fade" id="tab-assets">

                    {{-- Target Assets --}}
                    @if($job->targetAssets->isNotEmpty())
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="ri-list-check-3 me-2 text-primary"></i>Target Assets
                                @php $done = $job->targetAssets->where('completed', true)->count(); $total = $job->targetAssets->count(); @endphp
                                <span class="badge bg-success-subtle text-success ms-1">{{ $done }} / {{ $total }} completed</span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Asset Code</th>
                                            <th>Type</th>
                                            <th>Zone</th>
                                            <th>Completed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($job->targetAssets as $ta)
                                        <tr>
                                            <td class="ps-3">
                                                <a href="{{ route('admin.assets.show', $ta->asset) }}"
                                                   class="fw-medium text-primary text-decoration-none">
                                                    {{ $ta->asset->asset_code }}
                                                </a>
                                            </td>
                                            <td class="fs-13">{{ $assetTypes[$ta->asset->asset_type] ?? $ta->asset->asset_type }}</td>
                                            <td class="fs-13 text-muted">{{ $ta->asset->zone ?? '—' }}</td>
                                            <td>
                                                @if($ta->completed)
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="ri-checkbox-circle-line me-1"></i>Done
                                                    {{ $ta->completed_at?->format('d M Y') }}
                                                </span>
                                                @else
                                                <span class="badge bg-secondary-subtle text-secondary">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Installation Assets --}}
                    @if($job->installationAssets->isNotEmpty())
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0"><i class="ri-tools-line me-2 text-primary"></i>Installation / Rectification</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Asset Code</th>
                                            <th>Action</th>
                                            <th>Material Notes</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($job->installationAssets as $ia)
                                        <tr>
                                            <td class="ps-3">
                                                <a href="{{ route('admin.assets.show', $ia->asset) }}"
                                                   class="fw-medium text-primary text-decoration-none">
                                                    {{ $ia->asset->asset_code }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info">{{ ucfirst($ia->action) }}</span>
                                            </td>
                                            <td class="fs-13 text-muted">{{ $ia->material_notes ?? '—' }}</td>
                                            <td class="fs-13 text-muted">{{ $ia->created_at->format('d M Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Inspection Records --}}
                    @if($job->inspectionRecords->isNotEmpty())
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0"><i class="ri-survey-line me-2 text-primary"></i>Inspection Records</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Asset</th>
                                            <th>Date</th>
                                            <th>Result</th>
                                            <th>Status</th>
                                            <th>Technician</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($job->inspectionRecords as $ir)
                                        @php $rc = $resultColors[$ir->result] ?? 'secondary'; @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <a href="{{ route('admin.assets.show', $ir->asset) }}"
                                                   class="fw-medium text-primary text-decoration-none">
                                                    {{ $ir->asset->asset_code }}
                                                </a>
                                            </td>
                                            <td class="fs-13">{{ $ir->inspection_date->format('d M Y') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }}">
                                                    {{ ucwords(str_replace('_', ' ', $ir->result)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($ir->document_status === 'approved')
                                                <span class="badge bg-success-subtle text-success">Approved</span>
                                                @else
                                                <span class="badge bg-warning-subtle text-warning">Draft</span>
                                                @endif
                                            </td>
                                            <td class="fs-13">{{ $ir->technician->name ?? '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($job->targetAssets->isEmpty() && $job->installationAssets->isEmpty() && $job->inspectionRecords->isEmpty())
                    <div class="card">
                        <div class="card-body text-center text-muted py-5">
                            <i class="ri-tools-line fs-24 d-block mb-2"></i>
                            No assets linked to this job yet.
                        </div>
                    </div>
                    @endif

                </div>

            </div>
        </div>

        {{-- Right sidebar --}}
        <div class="col-lg-4">

            {{-- Status timeline --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="ri-git-branch-line me-2 text-primary"></i>Workflow</h6>
                </div>
                <div class="card-body">
                    @php
                    $allStatuses = array_keys(\App\Models\Job::STATUSES);
                    $currentIdx  = array_search($job->status, $allStatuses);
                    @endphp
                    <ul class="list-unstyled mb-0 vstack gap-2">
                        @foreach(\App\Models\Job::STATUSES as $sKey => $sLabel)
                        @php
                        $sIdx   = array_search($sKey, $allStatuses);
                        $isPast = $sIdx < $currentIdx;
                        $isCurr = $sKey === $job->status;
                        $sc2    = $statusColors[$sKey] ?? 'secondary';
                        @endphp
                        <li class="d-flex align-items-center gap-2 fs-13 {{ $isCurr ? 'fw-semibold' : ($isPast ? 'text-muted' : 'text-muted opacity-50') }}">
                            @if($isCurr)
                            <i class="ri-record-circle-line text-{{ $sc2 }}"></i>
                            @elseif($isPast)
                            <i class="ri-checkbox-circle-fill text-success"></i>
                            @else
                            <i class="ri-circle-line text-muted"></i>
                            @endif
                            {{ $sLabel }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Actions --}}
            @if(!$job->isClosed())
            <a href="{{ route('admin.jobs.edit', $job) }}" class="btn btn-primary w-100">
                <i class="ri-edit-line me-1"></i> Edit / Advance Status
            </a>
            @else
            <div class="alert alert-secondary fs-13 d-flex align-items-center gap-2">
                <i class="ri-lock-line fs-18"></i> This job is closed.
            </div>
            @endif

            {{-- Certificate Management (issued / closed only) --}}
            @if(in_array($job->status, ['issued', 'closed']))
            @php
                $clientUsers  = \App\Models\User::where('client_id', $job->client_id)->whereNotNull('email')->get();
                $clientEmail  = trim($job->client->email ?? '');
                $billingEmail = trim($job->client->billing_contact_info ?? '');

                // Collect unique valid recipient emails
                $recipients = collect();
                if (filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
                    $recipients->push($clientEmail);
                }
                foreach ($clientUsers->pluck('email') as $ue) {
                    if (!$recipients->contains($ue)) $recipients->push($ue);
                }
                if (filter_var($billingEmail, FILTER_VALIDATE_EMAIL) && !$recipients->contains($billingEmail)) {
                    $recipients->push($billingEmail);
                }

                $hasMail = $recipients->isNotEmpty();
            @endphp
            <div class="card mt-3 border-0 shadow-sm">
                <div class="card-header bg-dark text-white py-2 px-3">
                    <h6 class="mb-0 fs-13"><i class="ri-award-line me-2"></i>Certificate Management</h6>
                </div>
                <div class="card-body p-3">

                    {{-- Download --}}
                    <a href="{{ route('admin.jobs.certificate', $job) }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="ri-file-download-line me-1"></i>Download Certificate
                    </a>

                    {{-- Regenerate --}}
                    <a href="{{ route('admin.jobs.certificate', $job) }}" class="btn btn-outline-secondary w-100 mb-2"
                       id="regenerate-cert-btn">
                        <i class="ri-refresh-line me-1"></i>Regenerate Certificate
                    </a>

                    {{-- Send via Email --}}
                    @if($hasMail)
                    <form method="POST" action="{{ route('admin.jobs.sendCertificate', $job) }}" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-success w-100 send-cert-btn"
                                data-recipients="{{ $recipients->join(', ') }}">
                            <i class="ri-mail-send-line me-1"></i>
                            @if($job->certificate_sent_at)
                                Resend Certificate
                            @else
                                Send via Email
                            @endif
                        </button>
                    </form>
                    @if($job->certificate_sent_at)
                    <p class="text-muted fs-11 text-center mb-2">
                        <i class="ri-checkbox-circle-line text-success me-1"></i>
                        Sent {{ site_time($job->certificate_sent_at, $job->site->timezone) }}
                    </p>
                    @endif
                    <p class="text-muted fs-11 mb-2">
                        <i class="ri-mail-line me-1"></i>To: {{ $recipients->join(', ') }}
                    </p>
                    @else
                    <div class="alert alert-warning py-2 px-3 mb-2 fs-12">
                        <i class="ri-mail-close-line me-1"></i>
                        Cannot send — no email address found for this client.
                        <br><small>Add a client user with an email or set a valid billing contact email.</small>
                    </div>
                    @endif

                    {{-- Client Portal Access --}}
                    <form id="toggle-access-form" method="POST" action="{{ route('admin.jobs.toggleCertificateAccess', $job) }}">
                        @csrf
                        <button type="button" id="toggle-access-btn"
                                class="btn w-100 {{ $job->certificate_accessible ? 'btn-danger' : 'btn-outline-secondary' }}"
                                data-accessible="{{ $job->certificate_accessible ? '1' : '0' }}"
                                data-client="{{ $job->client->name ?? 'the client' }}">
                            @if($job->certificate_accessible)
                                <i class="ri-eye-off-line me-1"></i>Revoke Client Access
                            @else
                                <i class="ri-eye-line me-1"></i>Make Accessible to Client
                            @endif
                        </button>
                    </form>
                    @if($job->certificate_accessible)
                    <p class="text-success fs-11 text-center mt-1 mb-0">
                        <i class="ri-checkbox-circle-line me-1"></i>Client can download this certificate from their portal.
                    </p>
                    @endif

                </div>
            </div>
            @endif

        </div>

    </div>

@push('scripts')
<script>
    // Send via email confirmation
    document.querySelectorAll('.send-cert-btn').forEach(function (btn) {
        btn.closest('form').addEventListener('submit', function (e) {
            e.preventDefault();
            const form = this;
            const recipients = btn.dataset.recipients;
            Swal.fire({
                title: 'Send Certificate?',
                html: `<p class="mb-1">The certificate PDF will be emailed to:</p><p><strong>${recipients}</strong></p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-mail-send-line me-1"></i>Yes, Send',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#0ab39c',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    });

    // Regenerate certificate confirmation
    const regenBtn = document.getElementById('regenerate-cert-btn');
    if (regenBtn) {
        regenBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.href;
            Swal.fire({
                title: 'Regenerate Certificate?',
                html: '<p>A fresh certificate will be generated from the latest approved inspection data and downloaded.</p>',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '<i class="ri-refresh-line me-1"></i>Yes, Regenerate',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#405189',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) window.location.href = url;
            });
        });
    }

    // Toggle client access confirmation
    const toggleBtn = document.getElementById('toggle-access-btn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            const isAccessible = this.dataset.accessible === '1';
            const client = this.dataset.client;
            const form = document.getElementById('toggle-access-form');

            Swal.fire({
                title: isAccessible ? 'Revoke Client Access?' : 'Make Accessible to Client?',
                html: isAccessible
                    ? `<p><strong>${client}</strong> will no longer be able to download this certificate from their portal.</p>`
                    : `<p><strong>${client}</strong> will be able to download this certificate directly from their client portal.</p>`,
                icon: isAccessible ? 'warning' : 'info',
                showCancelButton: true,
                confirmButtonText: isAccessible
                    ? '<i class="ri-eye-off-line me-1"></i>Yes, Revoke'
                    : '<i class="ri-eye-line me-1"></i>Yes, Enable',
                cancelButtonText: 'Cancel',
                confirmButtonColor: isAccessible ? '#f06548' : '#0ab39c',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    }
</script>
@endpush
</x-app-layout>
