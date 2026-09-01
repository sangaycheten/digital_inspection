<x-app-layout>
    <x-slot name="title">Inspection Reports</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Inspection Reports</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Inspection Reports</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom pb-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <label class="form-label text-muted fs-12 mb-1">Work Type</label>
                    <select name="work_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach($workTypes as $val => $label)
                        <option value="{{ $val }}" {{ request('work_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i>Filter</button>
                    <a href="{{ route('client.reports.index') }}" class="btn btn-light btn-sm ms-1"><i class="ri-refresh-line"></i></a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Certificate No.</th>
                            <th>Site</th>
                            <th>Work Type</th>
                            <th class="text-center">Assets</th>
                            <th>Status</th>
                            <th>Issued Date</th>
                            <th class="text-end pe-3">Download</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobs as $job)
                        <tr>
                            <td class="ps-3 fw-semibold text-primary">
                                CERT-{{ strtoupper(substr($job->id, 0, 8)) }}
                            </td>
                            <td class="fs-13">{{ $job->site->name ?? $job->site->address ?? '—' }}</td>
                            <td>
                                <span class="badge bg-info-subtle text-info fs-11">
                                    {{ \App\Models\Job::WORK_TYPES[$job->work_type] }}
                                </span>
                            </td>
                            <td class="text-center fs-13">{{ $job->inspection_records_count }}</td>
                            <td>
                                <span class="badge bg-success-subtle text-success fs-11">
                                    {{ \App\Models\Job::STATUSES[$job->status] }}
                                </span>
                            </td>
                            <td class="text-muted fs-13">{{ ($job->certificate_sent_at ?? $job->updated_at)->format('d M Y') }}</td>
                            <td class="text-end pe-3">
                                <a href="{{ route('client.certificates.download', $job) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="ri-file-download-line me-1"></i>Download PDF
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ri-file-list-3-line fs-2 opacity-50 d-block mb-2"></i>
                                No inspection reports available yet.
                                <div class="fs-13 mt-1">Reports will appear here once your inspections have been completed and issued.</div>
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
</x-app-layout>
