<x-app-layout>
    <x-slot name="title">Asset — {{ $asset->asset_code }}</x-slot>

    @php
    $statusColors = [
        'pass'           => 'success',
        'fail'           => 'danger',
        'under_review'   => 'warning',
        'restricted_use' => 'orange',
        'not_inspected'  => 'secondary',
        'not_located'    => 'dark',
        'removed'        => 'dark',
        'replaced'       => 'info',
    ];
    $resultColors = [
        'pass'           => 'success',
        'fail'           => 'danger',
        'under_review'   => 'warning',
        'restricted_use' => 'orange',
        'not_inspected'  => 'secondary',
        'not_located'    => 'dark',
    ];
    $typeLabels = $assetTypes;
    $sc = $statusColors[$asset->current_status] ?? 'secondary';
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">
                    Asset — <span class="text-primary">{{ $asset->asset_code }}</span>
                    @if($sc === 'orange')
                    <span class="badge ms-2 fs-12" style="background-color:#fd7e14;color:#fff;">
                        {{ ucwords(str_replace('_', ' ', $asset->current_status)) }}
                    </span>
                    @else
                    <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} ms-2 fs-12">
                        {{ ucwords(str_replace('_', ' ', $asset->current_status)) }}
                    </span>
                    @endif
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.assets.index') }}">Asset Register</a></li>
                        <li class="breadcrumb-item active">{{ $asset->asset_code }}</li>
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

    <div class="row g-3">

        {{-- Left: asset details --}}
        <div class="col-lg-8">

            {{-- Tabs --}}
            <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-details" role="tab">
                        <i class="ri-tools-line me-1"></i> Asset Details
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-history" role="tab">
                        <i class="ri-history-line me-1"></i> Inspection History
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $asset->inspectionRecords->count() }}</span>
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
                                            <td class="text-muted ps-0 fs-13" style="width:140px">Site</td>
                                            <td class="fw-medium fs-13">{{ $asset->site->name ?? $asset->site->address }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Client</td>
                                            <td class="fs-13">{{ $asset->site->client->name ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Building</td>
                                            <td class="fs-13">{{ $asset->building->name_or_level ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Zone</td>
                                            <td class="fs-13">{{ $asset->zone ?? '—' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-3">Identity</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted ps-0 fs-13" style="width:140px">Asset Code</td>
                                            <td class="fw-semibold fs-13 text-primary">{{ $asset->asset_code }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Type</td>
                                            <td class="fs-13">
                                                <span class="badge bg-info-subtle text-info">
                                                    {{ $typeLabels[$asset->asset_type] ?? $asset->asset_type }}
                                                </span>
                                            </td>
                                        </tr>
                                        @if($asset->group_id)
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Group</td>
                                            <td class="fs-12 text-muted font-monospace">{{ $asset->group_id }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-3">Equipment</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted ps-0 fs-13" style="width:140px">Make</td>
                                            <td class="fs-13">{{ $asset->make ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Model</td>
                                            <td class="fs-13">{{ $asset->model ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Serial / Batch</td>
                                            <td class="fs-13">{{ $asset->serial_or_batch ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Rating</td>
                                            <td class="fs-13">{{ $asset->rating ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Fixing Type</td>
                                            <td class="fs-13">{{ $asset->fixing_type ?? '—' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-3">Dates</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted ps-0 fs-13" style="width:140px">Install Date</td>
                                            <td class="fs-13">{{ $asset->install_date?->format('d M Y') ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Next Inspection</td>
                                            <td class="fs-13">
                                                @php
                                                $due = $asset->next_inspection_due_date;
                                                $dueClass = $due && $due->isPast() ? 'text-danger fw-semibold' : ($due && $due->diffInDays(now()) <= 30 ? 'text-warning fw-semibold' : '');
                                                @endphp
                                                <span class="{{ $dueClass }}">
                                                    {{ $due?->format('d M Y') ?? '—' }}
                                                    @if($due && $due->isPast())
                                                    <span class="badge bg-danger-subtle text-danger ms-1">Overdue</span>
                                                    @elseif($due && $due->diffInDays(now()) <= 30)
                                                    <span class="badge bg-warning-subtle text-warning ms-1">Due soon</span>
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0 fs-13">Created</td>
                                            <td class="fs-13">{{ $asset->created_at->format('d M Y') }}</td>
                                        </tr>
                                    </table>
                                </div>

                                {{-- Replacement chain --}}
                                @if($asset->replacesAsset || $asset->replacedByAsset)
                                <div class="col-12">
                                    <hr class="my-1">
                                    <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-3">Replacement Chain</h6>
                                    <div class="hstack gap-3 flex-wrap">
                                        @if($asset->replacesAsset)
                                        <div>
                                            <div class="text-muted fs-12 mb-1">Replaces</div>
                                            <a href="{{ route('admin.assets.show', $asset->replacesAsset) }}"
                                               class="badge bg-secondary-subtle text-secondary text-decoration-none fs-12">
                                                <i class="ri-arrow-left-line me-1"></i>{{ $asset->replacesAsset->asset_code }}
                                            </a>
                                        </div>
                                        @endif
                                        @if($asset->replacedByAsset)
                                        <div>
                                            <div class="text-muted fs-12 mb-1">Replaced by</div>
                                            <a href="{{ route('admin.assets.show', $asset->replacedByAsset) }}"
                                               class="badge bg-info-subtle text-info text-decoration-none fs-12">
                                                {{ $asset->replacedByAsset->asset_code }}<i class="ri-arrow-right-line ms-1"></i>
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Inspection History Tab --}}
                <div class="tab-pane fade" id="tab-history">
                    <div class="card">
                        <div class="card-body p-0">
                            @if($asset->inspectionRecords->isEmpty())
                            <div class="text-center text-muted py-5">
                                <i class="ri-survey-line fs-24 d-block mb-2"></i>
                                No inspection records yet.
                            </div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Date</th>
                                            <th>Result</th>
                                            <th>Technician</th>
                                            <th>Condition</th>
                                            <th>Document Status</th>
                                            <th>Current</th>
                                            <th>Defects / Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($asset->inspectionRecords as $record)
                                        @php
                                        $rc = $resultColors[$record->result] ?? 'secondary';
                                        @endphp
                                        <tr class="{{ $record->is_current ? 'table-active' : '' }}">
                                            <td class="ps-3 fw-medium fs-13">
                                                {{ $record->inspection_date->format('d M Y') }}
                                            </td>
                                            <td>
                                                @if($rc === 'orange')
                                                <span class="badge" style="background-color:#fd7e14;color:#fff;">
                                                    {{ ucwords(str_replace('_', ' ', $record->result)) }}
                                                </span>
                                                @else
                                                <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }}">
                                                    {{ ucwords(str_replace('_', ' ', $record->result)) }}
                                                </span>
                                                @endif
                                            </td>
                                            <td class="fs-13">{{ $record->technician->name ?? '—' }}</td>
                                            <td class="fs-13">{{ $record->condition ?? '—' }}</td>
                                            <td>
                                                @if($record->document_status === 'approved')
                                                <span class="badge bg-success-subtle text-success">Approved</span>
                                                @else
                                                <span class="badge bg-warning-subtle text-warning">Draft</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($record->is_current)
                                                <span class="badge bg-primary-subtle text-primary">
                                                    <i class="ri-checkbox-circle-line me-1"></i>Current
                                                </span>
                                                @endif
                                            </td>
                                            <td class="fs-12 text-muted" style="max-width:200px">
                                                {{ $record->defect_description ?? $record->recommendation ?? '—' }}
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

            </div>{{-- /tab-content --}}
        </div>

        {{-- Right: summary card + actions --}}
        <div class="col-lg-4">

            {{-- Latest Inspection --}}
            @if($asset->currentInspection)
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="ri-survey-line me-2 text-primary"></i>Latest Approved Inspection</h6>
                </div>
                <div class="card-body">
                    @php $ir = $asset->currentInspection; $rc = $resultColors[$ir->result] ?? 'secondary'; @endphp
                    <div class="mb-2">
                        @if($rc === 'orange')
                        <span class="badge fs-13 px-3 py-2" style="background-color:#fd7e14;color:#fff;">
                            {{ ucwords(str_replace('_', ' ', $ir->result)) }}
                        </span>
                        @else
                        <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }} fs-13 px-3 py-2">
                            {{ ucwords(str_replace('_', ' ', $ir->result)) }}
                        </span>
                        @endif
                    </div>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted ps-0 fs-13" style="width:110px">Inspected</td>
                            <td class="fs-13">{{ $ir->inspection_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0 fs-13">Technician</td>
                            <td class="fs-13">{{ $ir->technician->name ?? '—' }}</td>
                        </tr>
                        @if($ir->condition)
                        <tr>
                            <td class="text-muted ps-0 fs-13">Condition</td>
                            <td class="fs-13">{{ $ir->condition }}</td>
                        </tr>
                        @endif
                        @if($ir->recommendation)
                        <tr>
                            <td class="text-muted ps-0 fs-13">Recommendation</td>
                            <td class="fs-13">{{ $ir->recommendation }}</td>
                        </tr>
                        @endif
                        @if($ir->required_action)
                        <tr>
                            <td class="text-muted ps-0 fs-13">Required Action</td>
                            <td class="fs-13 text-danger">{{ $ir->required_action }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            @endif

            {{-- Audit info --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="ri-history-line me-2 text-primary"></i>Audit</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted ps-0 fs-13" style="width:100px">Created by</td>
                            <td class="fs-13">{{ $asset->creator->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0 fs-13">Created at</td>
                            <td class="fs-13">{{ $asset->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                        @if($asset->editor)
                        <tr>
                            <td class="text-muted ps-0 fs-13">Updated by</td>
                            <td class="fs-13">{{ $asset->editor->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0 fs-13">Updated at</td>
                            <td class="fs-13">{{ $asset->updated_at->format('d M Y, H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Actions --}}
            @if(!in_array($asset->current_status, ['removed', 'replaced']))
            <div class="d-flex gap-2 flex-column">
                <a href="{{ route('admin.assets.edit', $asset) }}" class="btn btn-primary">
                    <i class="ri-edit-line me-1"></i> Edit Asset
                </a>
            </div>
            @else
            <div class="alert alert-secondary d-flex align-items-center gap-2 fs-13">
                <i class="ri-information-line fs-18"></i>
                This asset is <strong class="ms-1">{{ $asset->current_status }}</strong> and cannot be edited.
            </div>
            @endif

        </div>
    </div>

</x-app-layout>
