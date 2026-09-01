<x-app-layout>
    <x-slot name="title">Asset – {{ $asset->asset_code }}</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ $asset->asset_code }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('client.assets.index') }}">My Assets</a></li>
                        <li class="breadcrumb-item active">{{ $asset->asset_code }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @php
        $sc = match($asset->current_status) {
            'pass'           => 'success',
            'fail'           => 'danger',
            'restricted_use' => 'warning',
            'not_inspected'  => 'secondary',
            'not_located'    => 'dark',
            'removed'        => 'danger',
            default          => 'secondary',
        };
    @endphp

    <div class="row g-3">

        {{-- Asset Info --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="ri-tools-line me-2 text-primary"></i>Asset Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted ps-0 fs-13" style="width:40%">Asset Code</td>
                            <td class="fw-semibold fs-13">{{ $asset->asset_code }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0 fs-13">Type</td>
                            <td class="fs-13">{{ $assetTypes[$asset->asset_type] ?? $asset->asset_type }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0 fs-13">Status</td>
                            <td>
                                <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }}">
                                    {{ ucwords(str_replace('_', ' ', $asset->current_status ?? 'not_inspected')) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0 fs-13">Site</td>
                            <td class="fs-13">{{ $asset->site->name ?? $asset->site->address ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0 fs-13">Building</td>
                            <td class="fs-13">{{ $asset->building?->name_or_level ?? '—' }}</td>
                        </tr>
                        @if($asset->zone)
                        <tr>
                            <td class="text-muted ps-0 fs-13">Zone</td>
                            <td class="fs-13">{{ $asset->zone }}</td>
                        </tr>
                        @endif
                        @if($asset->location_description)
                        <tr>
                            <td class="text-muted ps-0 fs-13">Location</td>
                            <td class="fs-13">{{ $asset->location_description }}</td>
                        </tr>
                        @endif
                        @if($asset->install_date)
                        <tr>
                            <td class="text-muted ps-0 fs-13">Installed</td>
                            <td class="fs-13">{{ $asset->install_date->format('d M Y') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted ps-0 fs-13">Next Due</td>
                            <td class="fs-13">
                                @if($asset->next_inspection_due_date)
                                    @php $days = now()->diffInDays($asset->next_inspection_due_date, false); @endphp
                                    {{ $asset->next_inspection_due_date->format('d M Y') }}
                                    @if($days <= 0)
                                        <span class="badge bg-danger-subtle text-danger ms-1">Overdue</span>
                                    @elseif($days <= 30)
                                        <span class="badge bg-warning-subtle text-warning ms-1">{{ $days }}d</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Inspection History --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="ri-history-line me-2 text-primary"></i>Inspection History
                        <span class="badge bg-secondary-subtle text-secondary ms-2">{{ $asset->inspectionRecords->count() }}</span>
                    </h6>
                </div>
                @if($asset->inspectionRecords->isEmpty())
                <div class="card-body text-center text-muted py-5">
                    <i class="ri-clipboard-line fs-2 opacity-50 d-block mb-2"></i>
                    No inspections recorded yet.
                </div>
                @else
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Result</th>
                                    <th>Job</th>
                                    <th>Technician</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asset->inspectionRecords as $record)
                                @php
                                    $rc = match($record->result) {
                                        'pass'           => 'success',
                                        'fail'           => 'danger',
                                        'restricted_use' => 'warning',
                                        'not_located'    => 'dark',
                                        'removed'        => 'danger',
                                        default          => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="ps-3 fs-13 text-muted">
                                        {{ $record->inspection_date?->format('d M Y') ?? '—' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }}">
                                            {{ ucwords(str_replace('_', ' ', $record->result ?? '—')) }}
                                        </span>
                                    </td>
                                    <td class="fs-13 text-muted">
                                        @if($record->job)
                                            {{ \App\Models\Job::WORK_TYPES[$record->job->work_type] ?? $record->job->work_type }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="fs-13 text-muted">{{ $record->technician?->name ?? '—' }}</td>
                                    <td class="fs-13 text-muted" style="max-width:220px">
                                        @if($record->defect_description)
                                            <span title="{{ $record->defect_description }}" class="text-truncate d-inline-block" style="max-width:200px">
                                                {{ $record->defect_description }}
                                            </span>
                                        @elseif($record->recommendation)
                                            <span title="{{ $record->recommendation }}" class="text-truncate d-inline-block" style="max-width:200px">
                                                {{ $record->recommendation }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
