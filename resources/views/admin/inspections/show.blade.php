<x-app-layout>
    <x-slot name="title">Inspection — {{ $inspection->asset->asset_code }}</x-slot>

    @php
    $resultColors = [
        'pass'           => 'success',
        'fail'           => 'danger',
        'under_review'   => 'warning',
        'restricted_use' => 'warning',
        'not_inspected'  => 'secondary',
        'not_located'    => 'dark',
    ];
    $rc = $resultColors[$inspection->result] ?? 'secondary';
    $isRejected = $inspection->document_status === 'submitted'
        && str_starts_with($inspection->required_action ?? '', '[REJECTED]');
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">
                    Inspection — {{ $inspection->asset->asset_code }}
                    <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }} ms-2 fs-12">
                        {{ ucwords(str_replace('_', ' ', $inspection->result)) }}
                    </span>
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.inspections.index') }}">Inspections</a></li>
                        <li class="breadcrumb-item active">{{ $inspection->asset->asset_code }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if($isRejected)
    <div class="alert alert-danger alert-border-left">
        <i class="ri-arrow-go-back-line me-2"></i>
        <strong>Sent back for revision:</strong>
        {{ ltrim(str_replace('[REJECTED]', '', $inspection->required_action)) }}
    </div>
    @endif

    <div class="row g-3">

        {{-- Main detail --}}
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="ri-information-line me-2 text-primary"></i>Inspection Detail</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-2">Asset</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted ps-0 fs-13" style="width:130px">Code</td>
                                    <td class="fw-medium fs-13">
                                        <a href="{{ route('admin.assets.show', $inspection->asset) }}">{{ $inspection->asset->asset_code }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 fs-13">Type</td>
                                    <td class="fs-13">{{ $assetTypes[$inspection->asset->asset_type] ?? $inspection->asset->asset_type }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 fs-13">Zone</td>
                                    <td class="fs-13">{{ $inspection->asset->zone ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 fs-13">Client</td>
                                    <td class="fs-13">{{ $inspection->asset->site->client->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 fs-13">Site</td>
                                    <td class="fs-13">{{ $inspection->asset->site->name ?? $inspection->asset->site->address ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 fs-13">Building</td>
                                    <td class="fs-13">{{ $inspection->asset->building?->name_or_level ?? '—' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-2">Inspection</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted ps-0 fs-13" style="width:130px">Technician</td>
                                    <td class="fs-13">{{ $inspection->technician->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 fs-13">Date</td>
                                    <td class="fs-13">{{ $inspection->inspection_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 fs-13">Result</td>
                                    <td>
                                        <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }}">
                                            {{ ucwords(str_replace('_', ' ', $inspection->result)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 fs-13">Doc Status</td>
                                    <td>
                                        @if($inspection->document_status === 'approved')
                                        <span class="badge bg-success-subtle text-success"><i class="ri-checkbox-circle-line me-1"></i>Approved</span>
                                        @else
                                        <span class="badge bg-warning-subtle text-warning"><i class="ri-time-line me-1"></i>Pending Review</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 fs-13">Is Current</td>
                                    <td class="fs-13">{{ $inspection->is_current ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 fs-13">Submitted</td>
                                    <td class="fs-13 text-muted">{{ $inspection->created_at->format('d M Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>

                        @if($inspection->condition)
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-1">Condition</h6>
                            <p class="fs-13 mb-0">{{ $inspection->condition }}</p>
                        </div>
                        @endif

                        @if($inspection->defect_description)
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-1">Defect Description</h6>
                            <p class="fs-13 mb-0 text-danger">{{ $inspection->defect_description }}</p>
                        </div>
                        @endif

                        @if($inspection->reason_for_result)
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-1">Reason for Result</h6>
                            <p class="fs-13 mb-0">{{ $inspection->reason_for_result }}</p>
                        </div>
                        @endif

                        @if($inspection->recommendation)
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-1">Recommendation</h6>
                            <p class="fs-13 mb-0">{{ $inspection->recommendation }}</p>
                        </div>
                        @endif

                        @if($inspection->required_action && !$isRejected)
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-1">Required Action</h6>
                            <p class="fs-13 mb-0">{{ $inspection->required_action }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Checklist answers --}}
            @if($inspection->answers->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="ri-list-check-3 me-2 text-primary"></i>Inspection Checklist Answers
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $inspection->answers->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($inspection->answers as $answer)
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-muted fw-semibold fs-11 mb-1">
                                {{ $answer->questionnaire->name ?? '—' }}
                            </h6>
                            <p class="fs-13 mb-0">{{ $answer->answer_value ?? '—' }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Assessment trail --}}
            @include('partials.inspection-trail')

            {{-- Previous inspection for comparison --}}
            @if($inspection->previousInspection)
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0 text-muted">
                        <i class="ri-history-line me-2"></i>Previous Inspection
                        <span class="fs-12 fw-normal ms-1">({{ $inspection->previousInspection->inspection_date->format('d M Y') }})</span>
                    </h6>
                </div>
                <div class="card-body">
                    @php $prevRc = $resultColors[$inspection->previousInspection->result] ?? 'secondary'; @endphp
                    <div class="row g-2 fs-13">
                        <div class="col-md-3">
                            <span class="text-muted">Result</span><br>
                            <span class="badge bg-{{ $prevRc }}-subtle text-{{ $prevRc }}">
                                {{ ucwords(str_replace('_', ' ', $inspection->previousInspection->result)) }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted">Technician</span><br>
                            {{ $inspection->previousInspection->technician->name ?? '—' }}
                        </div>
                        @if($inspection->previousInspection->condition)
                        <div class="col-md-6">
                            <span class="text-muted">Condition</span><br>
                            {{ $inspection->previousInspection->condition }}
                        </div>
                        @endif
                        @if($inspection->previousInspection->defect_description)
                        <div class="col-12">
                            <span class="text-muted">Defects</span><br>
                            {{ $inspection->previousInspection->defect_description }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Info sidebar --}}
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="ri-shield-check-line me-2 text-primary"></i>Document Status</h6>
                </div>
                <div class="card-body">
                    @if($inspection->document_status === 'approved')
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-0">
                        <i class="ri-checkbox-circle-fill fs-18"></i>
                        <span>This inspection has been approved.</span>
                    </div>
                    @elseif($isRejected)
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-0">
                        <i class="ri-arrow-go-back-line fs-18"></i>
                        <span>Sent back for revision.</span>
                    </div>
                    @else
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-0">
                        <i class="ri-time-line fs-18"></i>
                        <span>Awaiting review by manager.</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="ri-links-line me-2 text-primary"></i>Quick Links</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('admin.assets.show', $inspection->asset) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ri-tools-line me-1"></i>View Asset
                    </a>
                    <a href="{{ route('admin.inspections.index') }}" class="btn btn-light btn-sm">
                        <i class="ri-arrow-left-line me-1"></i>Back to List
                    </a>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
