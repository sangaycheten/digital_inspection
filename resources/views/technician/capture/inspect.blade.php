<x-app-layout>
    <x-slot name="title">Record Inspections</x-slot>

    @php
    $resultColors = [
        'pass'           => 'success',
        'fail'           => 'danger',
        'under_review'   => 'warning',
        'restricted_use' => 'warning',
        'not_inspected'  => 'secondary',
        'not_located'    => 'dark',
    ];
    $byType        = $grouped->flatten(1)->groupBy('asset_type');
    $stepCount     = $byType->count() + 1; // asset-type steps + review step
    $allSubmitted  = $doneAssetIds->count() >= $byType->flatten()->count() && $byType->flatten()->isNotEmpty();
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Record Inspections</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('technician.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('technician.jobs.index') }}">My Jobs</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('technician.jobs.show', $job) }}">Job</a></li>
                        <li class="breadcrumb-item active">Inspect</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Job summary strip --}}
    <div class="alert alert-primary alert-border-left mb-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div>
                <span class="fw-semibold">{{ $job->client->name ?? '—' }}</span>
                <span class="text-muted mx-1">·</span>
                {{ $job->site->name ?? $job->site->address }}
            </div>
            <span class="badge bg-info-subtle text-info">{{ \App\Models\Job::WORK_TYPES[$job->work_type] }}</span>
            @if($job->buildings->isNotEmpty())
            <span class="text-muted fs-12">Buildings:
                @foreach($job->buildings as $b)
                <span class="badge bg-light text-dark border me-1">{{ $b->name_or_level }}</span>
                @endforeach
            </span>
            @endif
        </div>
    </div>

    @if(session('draft_saved'))
    <div class="alert alert-info alert-border-left alert-dismissible fade show">
        <i class="ri-save-3-line me-2"></i>Draft saved. You can continue inspecting where you left off.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-border-left alert-dismissible fade show">
        <i class="ri-error-warning-line me-2"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($lockedBuildings->isNotEmpty())
    <div class="alert alert-warning alert-border-left">
        <i class="ri-lock-line me-2 fs-16 align-middle"></i>
        <strong>Building(s) already being inspected by another technician:</strong>
        @foreach($lockedBuildings as $lb)
        <span class="badge bg-warning-subtle text-warning border border-warning ms-1">{{ $lb->name_or_level }}</span>
        @endforeach
        <div class="fs-12 mt-1 text-muted">These buildings are excluded from your form. Another technician has already started inspection there.</div>
    </div>
    @endif

    <form method="POST" action="{{ route('technician.jobs.inspect.store', $job) }}" id="inspectForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="save_as_draft" id="saveDraftInput" value="0">

        {{-- Inspection date --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Inspection Date <span class="text-danger">*</span></label>
                        <input type="date" name="inspection_date"
                               class="form-control @error('inspection_date') is-invalid @enderror"
                               value="{{ old('inspection_date', date('Y-m-d')) }}" required>
                        @error('inspection_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8 d-flex align-items-center">
                        <span class="text-muted fs-13">
                            <span id="inspectedCount" class="fw-semibold text-primary">{{ $doneAssetIds->count() }}</span>
                            of {{ $byType->flatten()->count() }} asset(s) recorded
                        </span>
                    </div>
                </div>
            </div>
        </div>

        @if($byType->isEmpty())
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="ri-tools-line fs-24 d-block mb-2"></i>
                @if($lockedBuildings->isNotEmpty())
                All buildings assigned to you are currently being inspected by another technician.
                @else
                No assets found for your assigned buildings. Ensure assets have been registered.
                @endif
            </div>
        </div>
        @else

        <div class="row g-3">

            {{-- ── Left: Step nav ─────────────────────────────────────────────── --}}
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body p-2">

                        @foreach($byType as $assetType => $typeAssets)
                        @php
                            $stepIdx     = $loop->index;
                            $preDone     = $typeAssets->filter(fn($a) => $doneAssetIds->contains($a->id))->count();
                            $stepResults = $typeAssets
                                ->map(fn($a) => $lockedRecords->get($a->id)?->result)
                                ->filter()->countBy();
                        @endphp
                        <div class="wizard-nav-item d-flex align-items-center gap-2 p-2 rounded mb-1"
                             data-step="{{ $stepIdx }}" role="button" onclick="goToStep({{ $stepIdx }})">
                            <div class="step-circle" data-step="{{ $stepIdx }}">
                                <span class="step-num">{{ $loop->iteration }}</span>
                                <i class="ri-check-line step-check d-none"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fs-13 fw-medium text-truncate">
                                    {{ $assetTypes[$assetType] ?? $assetType }}
                                </div>
                                <div class="fs-11 text-muted">
                                    <span class="nav-done-count" data-step="{{ $stepIdx }}">{{ $preDone }}</span>
                                    / {{ $typeAssets->count() }} recorded
                                </div>
                                @if($stepResults->isNotEmpty())
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($stepResults as $res => $cnt)
                                    @php $rc = $resultColors[$res] ?? 'secondary'; @endphp
                                    <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }}" style="font-size:10px">
                                        {{ ucwords(str_replace('_', ' ', $res)) }} {{ $cnt }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach

                        {{-- Review step --}}
                        <div class="wizard-nav-item d-flex align-items-center gap-2 p-2 rounded mb-1"
                             data-step="{{ $byType->count() }}" role="button" onclick="goToStep({{ $byType->count() }})">
                            <div class="step-circle" data-step="{{ $byType->count() }}">
                                <i class="ri-flag-2-line" style="font-size:14px"></i>
                            </div>
                            <div class="fs-13 fw-medium">Review & Save</div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── Center: Step panels ─────────────────────────────────────────── --}}
            <div class="col-lg-6">

                @foreach($byType as $assetType => $typeAssets)
                @php
                    $stepIdx = $loop->index;
                    $bulkQs  = $questionsByType->get($assetType, collect());
                @endphp

                <div class="wizard-panel" data-step="{{ $stepIdx }}"
                     @if(!$loop->first) style="display:none" @endif>
                    <div class="card mb-0">
                        <div class="card-header d-flex align-items-center gap-2">
                            <div class="form-check mb-0 me-1">
                                <input class="form-check-input select-all-cb" type="checkbox"
                                       data-step="{{ $stepIdx }}" id="selectAll_{{ $stepIdx }}"
                                       title="Select all">
                            </div>
                            <h6 class="card-title mb-0 flex-grow-1">
                                <i class="ri-tag-3-line me-2 text-primary"></i>
                                {{ $assetTypes[$assetType] ?? $assetType }}
                                <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $typeAssets->count() }}</span>
                            </h6>
                            <button type="button"
                                    class="btn btn-sm btn-success bulk-fill-btn d-none"
                                    data-step="{{ $stepIdx }}"
                                    onclick="toggleBulkPanel({{ $stepIdx }})">
                                <i class="ri-stack-line me-1"></i>Bulk Fill
                                (<span class="bulk-count-lbl" data-step="{{ $stepIdx }}">0</span>)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary expand-step-btn">
                                <i class="ri-expand-up-down-line me-1"></i>Expand All
                            </button>
                        </div>

                        {{-- Bulk Fill Panel --}}
                        <div class="bulk-panel d-none border-bottom bg-success-subtle px-3 py-3"
                             id="bulkPanel_{{ $stepIdx }}">
                            <p class="fs-12 fw-semibold text-success mb-2">
                                <i class="ri-stack-line me-1"></i>
                                Bulk Fill — applying to <span class="bulk-count-lbl" data-step="{{ $stepIdx }}">0</span> selected asset(s)
                            </p>

                            @if($bulkQs->isNotEmpty())
                            <p class="fs-11 text-uppercase text-muted fw-semibold mb-1">
                                <i class="ri-list-check-3 me-1"></i>Inspection Checklist
                            </p>
                            <div class="d-flex flex-column gap-2 mb-2">
                                @foreach($bulkQs as $bq)
                                @php $bqFt = $bq->fieldType; @endphp
                                <div class="border rounded p-2 bg-white">
                                    <label class="form-label fs-12 fw-medium mb-1 d-flex align-items-center gap-1">
                                        <span class="badge bg-secondary-subtle text-secondary fw-semibold" style="min-width:20px">{{ $loop->iteration }}</span>
                                        {{ $bq->name }}
                                    </label>
                                    @if($bq->type === 'long_text')
                                        <textarea class="form-control form-control-sm bulk-q-input" data-qid="{{ $bq->id }}" rows="2"></textarea>
                                    @elseif(in_array($bq->type, ['switch', 'option_list']) && $bqFt)
                                        <select class="form-select form-select-sm bulk-q-input" data-qid="{{ $bq->id }}">
                                            <option value="">— select —</option>
                                            @foreach($bqFt->options ?? [] as $opt)
                                            <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($bq->type === 'numeric')
                                        <input type="number" step="any" class="form-control form-control-sm bulk-q-input" data-qid="{{ $bq->id }}">
                                    @else
                                        <input type="text" class="form-control form-control-sm bulk-q-input" data-qid="{{ $bq->id }}">
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @endif

                            <div class="d-flex flex-column gap-2 mb-3">
                                <div>
                                    <label class="form-label fs-12 text-muted mb-1">Result <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm bulk-result" id="bulkResult_{{ $stepIdx }}">
                                        <option value="">— select —</option>
                                        @foreach(\App\Models\InspectionRecord::RESULTS as $r)
                                        <option value="{{ $r }}">{{ ucwords(str_replace('_', ' ', $r)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label fs-12 text-muted mb-1">Recommendation</label>
                                    <textarea class="form-control form-control-sm bulk-recommendation" id="bulkRec_{{ $stepIdx }}" rows="2" placeholder="Recommendation…"></textarea>
                                </div>
                                <div>
                                    <label class="form-label fs-12 text-muted mb-1">Required Action</label>
                                    <textarea class="form-control form-control-sm bulk-required-action" id="bulkAction_{{ $stepIdx }}" rows="2" placeholder="Action required…"></textarea>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success btn-sm" onclick="applyBulk({{ $stepIdx }})">
                                    <i class="ri-check-double-line me-1"></i>Apply to <span class="bulk-count-lbl" data-step="{{ $stepIdx }}">0</span> Selected
                                </button>
                                <button type="button" class="btn btn-light btn-sm" onclick="toggleBulkPanel({{ $stepIdx }})">Cancel</button>
                            </div>
                        </div>

                        <div class="card-body p-0">

                            @foreach($typeAssets as $asset)
                            @php
                                $isDone          = $doneAssetIds->contains($asset->id);
                                $doneStatus      = $doneAssetStatuses->get($asset->id);
                                $hasDraft        = $existingRecords->has($asset->id);
                                $isTarget        = $targetAssetIds->contains($asset->id);
                                $prevResult      = $asset->currentInspection?->result;
                                $prevColor       = $resultColors[$prevResult] ?? 'secondary';
                                $collapseId      = 'asset_' . $asset->id;
                                $assetQs         = $questionsByType->get($asset->asset_type, collect());
                                $existingRec     = $existingRecords->get($asset->id);
                                $existingAnswers = $existingRec?->answers->keyBy('questionnaire_id') ?? collect();
                                $lockedRec       = $lockedRecords->get($asset->id);
                                $lockedAnswers   = $lockedRec?->answers->keyBy('questionnaire_id') ?? collect();
                                $lockedColor     = $lockedRec ? ($resultColors[$lockedRec->result] ?? 'secondary') : null;
                                $rowBg           = $isDone && $lockedColor
                                    ? "bg-{$lockedColor}-subtle"
                                    : ($hasDraft ? 'bg-warning-subtle bg-opacity-50' : '');
                            @endphp

                            <div class="border-bottom asset-row {{ $rowBg }}"
                                 data-asset-id="{{ $asset->id }}">

                                <div class="d-flex align-items-center px-3 py-2 gap-2">
                                    @if(!$isDone)
                                    <input type="checkbox" class="form-check-input asset-select-cb flex-shrink-0"
                                           data-step="{{ $stepIdx }}" data-asset="{{ $asset->id }}"
                                           style="width:16px;height:16px;cursor:pointer">
                                    @else
                                    <span style="width:16px;display:inline-block;flex-shrink:0"></span>
                                    @endif
                                    <div class="flex-grow-1">
                                        <span class="fw-medium fs-13">{{ $asset->asset_code }}</span>
                                        @if($asset->building)
                                        <span class="text-muted fs-12 ms-2">{{ $asset->building->name_or_level }}</span>
                                        @endif
                                        @if($asset->zone)
                                        <span class="text-muted fs-12 ms-1">· {{ $asset->zone }}</span>
                                        @endif
                                        @if($isTarget)
                                        <span class="badge bg-warning-subtle text-warning ms-2 fs-11">Target</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($prevResult)
                                        <span class="badge bg-{{ $prevColor }}-subtle text-{{ $prevColor }} fs-11">
                                            Last: {{ ucwords(str_replace('_', ' ', $prevResult)) }}
                                        </span>
                                        @else
                                        <span class="badge bg-secondary-subtle text-secondary fs-11">No prior</span>
                                        @endif

                                        @if($isDone)
                                        @if($doneStatus === 'approved')
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="ri-shield-check-line me-1"></i>Approved
                                        </span>
                                        @else
                                        <span class="badge bg-primary-subtle text-primary">
                                            <i class="ri-send-plane-line me-1"></i>Submitted
                                        </span>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}">
                                            <i class="ri-eye-line me-1"></i>View
                                        </button>
                                        @elseif($hasDraft)
                                        @if(str_starts_with($existingRec?->required_action ?? '', '[REJECTED]'))
                                        <span class="badge bg-danger-subtle text-danger">
                                            <i class="ri-arrow-go-back-line me-1"></i>Sent Back
                                        </span>
                                        @else
                                        <span class="badge bg-warning-subtle text-warning">
                                            <i class="ri-save-3-line me-1"></i>Draft
                                        </span>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-warning toggle-inspect"
                                                data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}">
                                            <i class="ri-edit-line me-1"></i>Edit & Re-submit
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-outline-primary toggle-inspect"
                                                data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}">
                                            <i class="ri-add-line me-1"></i>Inspect
                                        </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- Read-only view for submitted/approved assets --}}
                                @if($isDone && $lockedRec)
                                <div class="collapse" id="{{ $collapseId }}">
                                    <div class="px-3 pb-3 pt-1 bg-light bg-opacity-50">
                                        @if($assetQs->isNotEmpty())
                                        <hr class="my-2">
                                        <p class="fs-11 text-uppercase text-muted fw-semibold mb-2">
                                            <i class="ri-list-check-3 me-1"></i>Inspection Checklist
                                        </p>
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($assetQs as $q)
                                            @php
                                                $qVal     = $lockedAnswers->get($q->id)?->answer_value ?? '—';
                                                $qNum     = $loop->iteration;
                                                $sqLetterCounters = [];
                                            @endphp
                                            <div class="border rounded p-2 bg-white">
                                                <p class="fs-12 fw-medium mb-1 d-flex align-items-center gap-1">
                                                    <span class="badge bg-secondary-subtle text-secondary fw-semibold" style="min-width:20px">{{ $qNum }}</span>
                                                    {{ $q->name }}
                                                </p>
                                                <p class="fs-13 mb-0 ps-1 text-dark">{{ $qVal }}</p>

                                                @foreach($q->subQuestionnaires as $sq)
                                                @php
                                                    $sqVal    = $lockedAnswers->get($sq->id)?->answer_value ?? null;
                                                    $sqCondKey = $sq->condition ?? '__none__';
                                                    $sqLetterCounters[$sqCondKey] = $sqLetterCounters[$sqCondKey] ?? 0;
                                                    $sqLetter = chr(ord('a') + $sqLetterCounters[$sqCondKey]++);
                                                @endphp
                                                @if($sqVal !== null)
                                                <div class="mt-1 ps-2 border-start border-2 border-secondary-subtle">
                                                    <p class="fs-11 text-muted mb-0 d-flex align-items-center gap-1">
                                                        <span class="badge bg-light text-secondary border fw-semibold" style="min-width:18px;font-size:10px">{{ $sqLetter }}</span>
                                                        {{ $sq->name }}
                                                    </p>
                                                    <p class="fs-12 mb-0 ps-1">{{ $sqVal }}</p>
                                                </div>
                                                @endif
                                                @endforeach
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif

                                        <hr class="my-2">
                                        <div class="d-flex flex-column gap-2">
                                            <div>
                                                <p class="fs-12 text-muted mb-1">Result</p>
                                                @php $rc = $resultColors[$lockedRec->result] ?? 'secondary'; @endphp
                                                <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }} fs-12">
                                                    {{ ucwords(str_replace('_', ' ', $lockedRec->result)) }}
                                                </span>
                                            </div>
                                            @if($lockedRec->recommendation)
                                            <div>
                                                <p class="fs-12 text-muted mb-1">Recommendation</p>
                                                <p class="fs-13 mb-0">{{ $lockedRec->recommendation }}</p>
                                            </div>
                                            @endif
                                            @if($lockedRec->required_action)
                                            <div>
                                                <p class="fs-12 text-muted mb-1">Required Action</p>
                                                <p class="fs-13 mb-0">{{ $lockedRec->required_action }}</p>
                                            </div>
                                            @endif
                                            @if($lockedRec->photo_path)
                                            <div>
                                                <p class="fs-12 text-muted mb-1"><i class="ri-camera-line me-1"></i>Photo</p>
                                                <a href="{{ \Illuminate\Support\Facades\Storage::url($lockedRec->photo_path) }}" target="_blank">
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($lockedRec->photo_path) }}"
                                                         class="img-thumbnail" style="max-height:150px">
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Editable form for draft / new assets --}}
                                @elseif(!$isDone)
                                <div class="collapse" id="{{ $collapseId }}">
                                    <div class="px-3 pb-3 pt-1 bg-light bg-opacity-50">

                                        @php
                                        $rejectionNote = null;
                                        if ($existingRec && str_starts_with($existingRec->required_action ?? '', '[REJECTED]')) {
                                            $rejectionNote = ltrim(str_replace('[REJECTED]', '', $existingRec->required_action));
                                        }
                                        @endphp
                                        @if($rejectionNote)
                                        <div class="alert alert-danger py-2 px-3 mt-2 mb-0 fs-13">
                                            <i class="ri-arrow-go-back-line me-1"></i>
                                            <strong>Sent back for revision:</strong> {{ $rejectionNote }}
                                        </div>
                                        @endif

                                        <hr class="my-2">
                                        @if($assetQs->isNotEmpty())
                                        <p class="fs-11 text-uppercase text-muted fw-semibold mb-2">
                                            <i class="ri-list-check-3 me-1"></i>Inspection Checklist
                                            <span class="badge bg-primary-subtle text-primary fw-normal ms-1">{{ $assetQs->count() }}</span>
                                        </p>
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($assetQs as $q)
                                            @php
                                                $fieldType = $q->fieldType;
                                                $inputName = "answers[{$asset->id}][{$q->id}]";
                                                $oldVal    = old("answers.{$asset->id}.{$q->id}") ?? $existingAnswers->get($q->id)?->answer_value;
                                                $qNum      = $loop->iteration;
                                            @endphp
                                            <div class="border rounded p-2 bg-white">
                                                <label class="form-label fs-12 fw-medium mb-1 d-flex align-items-center gap-1">
                                                    <span class="badge bg-secondary-subtle text-secondary fw-semibold" style="min-width:20px">{{ $qNum }}</span>
                                                    {{ $q->name }}
                                                    @if($q->required)<span class="text-danger">*</span>@endif
                                                </label>
                                                @if($q->type === 'long_text')
                                                    <textarea name="{{ $inputName }}" class="form-control form-control-sm" rows="2"
                                                              @if($q->required) required @endif>{{ $oldVal }}</textarea>
                                                @elseif(in_array($q->type, ['switch', 'option_list']) && $fieldType)
                                                @php $hasConditionalSubs = $q->type === 'switch' && $q->subQuestionnaires->whereNotNull('condition')->isNotEmpty(); @endphp
                                                    <select name="{{ $inputName }}" class="form-select form-select-sm"
                                                            @if($hasConditionalSubs) data-asset="{{ $asset->id }}" data-qid="{{ $q->id }}" onchange="onSubTriggerChange(this)" @endif
                                                            @if($q->required) required @endif>
                                                        <option value="">— select —</option>
                                                        @foreach($fieldType->options ?? [] as $opt)
                                                        <option value="{{ $opt }}" {{ $oldVal === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif($q->type === 'numeric')
                                                    <input type="number" step="any" name="{{ $inputName }}"
                                                           class="form-control form-control-sm"
                                                           value="{{ $oldVal }}"
                                                           @if($q->required) required @endif>
                                                @else
                                                    <input type="text" name="{{ $inputName }}"
                                                           class="form-control form-control-sm"
                                                           value="{{ $oldVal }}"
                                                           @if($q->required) required @endif>
                                                @endif

                                                @php $sqLetterCounters = []; @endphp
                                                @foreach($q->subQuestionnaires as $sq)
                                                @php
                                                    $sqName    = "answers[{$asset->id}][{$sq->id}]";
                                                    $sqOldVal  = old("answers.{$asset->id}.{$sq->id}") ?? $existingAnswers->get($sq->id)?->answer_value;
                                                    $sqFt      = $sq->fieldType;
                                                    $sqCondKey = $sq->condition ?? '__none__';
                                                    $sqLetterCounters[$sqCondKey] = $sqLetterCounters[$sqCondKey] ?? 0;
                                                    $sqLetter  = chr(ord('a') + $sqLetterCounters[$sqCondKey]++);
                                                    $sqVisible = !$sq->condition || $q->type !== 'switch' || ($oldVal && (
                                                        ($sq->condition === 'yes' && $oldVal === ($fieldType->options[0] ?? null)) ||
                                                        ($sq->condition === 'no'  && $oldVal === ($fieldType->options[1] ?? null))
                                                    ));
                                                @endphp
                                                <div class="mt-2 ps-2 border-start border-2 border-secondary-subtle"
                                                     @if($sq->condition) data-sq-parent="{{ $asset->id }}_{{ $q->id }}" data-sq-cond="{{ $sq->condition }}" @endif
                                                     @if(!$sqVisible) style="display:none;" @endif>
                                                    <label class="form-label fs-11 text-muted mb-1 d-flex align-items-center gap-1">
                                                        <span class="badge bg-light text-secondary border fw-semibold" style="min-width:18px;font-size:10px">{{ $sqLetter }}</span>
                                                        {{ $sq->name }}
                                                        @if($sq->required)<span class="text-danger">*</span>@endif
                                                    </label>
                                                    @if($sq->type === 'long_text')
                                                        <textarea name="{{ $sqName }}" class="form-control form-control-sm" rows="1"
                                                                  @if($sq->required) required @endif>{{ $sqOldVal }}</textarea>
                                                    @elseif(in_array($sq->type, ['switch', 'option_list']) && $sqFt)
                                                        <select name="{{ $sqName }}" class="form-select form-select-sm"
                                                                @if($sq->required) required @endif>
                                                            <option value="">— select —</option>
                                                            @foreach($sqFt->options ?? [] as $opt)
                                                            <option value="{{ $opt }}" {{ $sqOldVal === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($sq->type === 'numeric')
                                                        <input type="number" step="any" name="{{ $sqName }}"
                                                               class="form-control form-control-sm"
                                                               value="{{ $sqOldVal }}"
                                                               @if($sq->required) required @endif>
                                                    @else
                                                        <input type="text" name="{{ $sqName }}"
                                                               class="form-control form-control-sm"
                                                               value="{{ $sqOldVal }}"
                                                               @if($sq->required) required @endif>
                                                    @endif
                                                </div>
                                                @endforeach
                                            </div>
                                            @endforeach
                                        </div>
                                        @else
                                        <p class="fs-12 text-muted mb-2">
                                            <i class="ri-information-line me-1"></i>No checklist configured for this asset type. Record the result below.
                                        </p>
                                        @endif

                                        <div class="d-flex flex-column gap-2">
                                            <div>
                                                <label class="form-label fs-12 text-muted mb-1">Result <span class="text-danger">*</span></label>
                                                <select name="assets[{{ $asset->id }}][result]"
                                                        class="form-select form-select-sm result-select"
                                                        data-asset-id="{{ $asset->id }}"
                                                        data-step="{{ $stepIdx }}">
                                                    <option value="">— select —</option>
                                                    @foreach(\App\Models\InspectionRecord::RESULTS as $r)
                                                    <option value="{{ $r }}" {{ (old("assets.{$asset->id}.result") ?? $existingRec?->result) == $r ? 'selected' : '' }}>
                                                        {{ ucwords(str_replace('_', ' ', $r)) }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label fs-12 text-muted mb-1">Recommendation</label>
                                                <textarea name="assets[{{ $asset->id }}][recommendation]"
                                                          class="form-control form-control-sm" rows="2"
                                                          placeholder="Recommendation…">{{ old("assets.{$asset->id}.recommendation") ?? $existingRec?->recommendation }}</textarea>
                                            </div>
                                            <div>
                                                <label class="form-label fs-12 text-muted mb-1">Required Action</label>
                                                <textarea name="assets[{{ $asset->id }}][required_action]"
                                                          class="form-control form-control-sm" rows="2"
                                                          placeholder="Action required…">{{ old("assets.{$asset->id}.required_action") ?? ($rejectionNote ? '' : $existingRec?->required_action) }}</textarea>
                                            </div>
                                            <div>
                                                <label class="form-label fs-12 text-muted mb-1">
                                                    <i class="ri-camera-line me-1"></i>Photo
                                                </label>
                                                @if($existingRec?->photo_path)
                                                <div class="mb-2">
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($existingRec->photo_path) }}"
                                                         class="img-thumbnail" style="max-height:120px; cursor:pointer"
                                                         onclick="window.open(this.src,'_blank')">
                                                    <div class="fs-11 text-muted mt-1">Current photo — upload a new one to replace it</div>
                                                </div>
                                                @endif
                                                <input type="file" name="photos[{{ $asset->id }}]"
                                                       class="form-control form-control-sm"
                                                       accept="image/*">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                @endif {{-- end @if(!$isDone) collapse wrapper --}}

                            </div>
                            @endforeach

                        </div>
                    </div>
                </div>
                @endforeach

                {{-- Review & Save panel --}}
                <div class="wizard-panel" data-step="{{ $byType->count() }}" style="display:none">
                    <div class="card mb-0">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="ri-flag-2-line me-2 text-primary"></i>Review & Save
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted fs-13 mb-3">
                                Only assets with a result selected will be submitted. Submitted records cannot be edited.
                            </p>
                            <div id="reviewSummary"></div>
                        </div>
                    </div>
                </div>

                {{-- Navigation buttons --}}
                <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
                    <button type="button" class="btn btn-light" id="prevBtn" disabled onclick="changeStep(-1)">
                        <i class="ri-arrow-left-line me-1"></i>Back
                    </button>
                    <div class="d-flex gap-2">
                        @if(!$allSubmitted)
                        <button type="button" class="btn btn-outline-secondary" id="saveDraftBtn" onclick="saveDraft()">
                            <i class="ri-save-3-line me-1"></i>Save Draft
                        </button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">
                            Next <i class="ri-arrow-right-line ms-1"></i>
                        </button>
                        <button type="submit" class="btn btn-success d-none" id="saveBtn">
                            <i class="ri-send-plane-line me-1"></i>Submit Inspection Records
                        </button>
                        @endif
                        <a href="{{ route('technician.jobs.show', $job) }}" class="btn btn-light">
                            {{ $allSubmitted ? 'Back to Job' : 'Cancel' }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- ── Right: Progress panel ───────────────────────────────────────── --}}
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ri-bar-chart-line me-2 text-primary"></i>Inspection Progress
                        </h6>
                    </div>
                    <div class="card-body">
                        @php $grandTotal = $byType->flatten()->count(); @endphp

                        @foreach($byType as $assetType => $typeAssets)
                        @php
                            $stepIdx = $loop->index;
                            $preDone = $typeAssets->filter(fn($a) => $doneAssetIds->contains($a->id))->count();
                            $total   = $typeAssets->count();
                            $pct     = $total ? round($preDone / $total * 100) : 0;
                        @endphp
                        <div class="mb-3"
                             data-progress-step="{{ $stepIdx }}"
                             data-total="{{ $total }}"
                             data-predone="{{ $preDone }}"
                             data-type-label="{{ $assetTypes[$assetType] ?? $assetType }}">
                            <div class="d-flex justify-content-between align-items-center fs-12 mb-1">
                                <span class="text-muted text-truncate me-2" style="max-width:120px">
                                    {{ $assetTypes[$assetType] ?? $assetType }}
                                </span>
                                <span class="fw-medium text-nowrap">
                                    <span class="progress-done-count">{{ $preDone }}</span> / {{ $total }}
                                </span>
                            </div>
                            <div class="progress" style="height:5px">
                                <div class="progress-bar bg-success" style="width:{{ $pct }}%" role="progressbar"></div>
                            </div>
                        </div>
                        @endforeach

                        <hr class="my-2">
                        <div class="d-flex justify-content-between fs-13">
                            <span class="text-muted">Total</span>
                            <span class="fw-semibold">
                                <span id="totalDoneCount">{{ $doneAssetIds->count() }}</span> / {{ $grandTotal }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        @endif

    </form>

    @push('styles')
    <style>
    .step-circle {
        width: 30px; height: 30px; min-width: 30px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 600;
        border: 2px solid #dee2e6;
        color: #6c757d;
        background: transparent;
        transition: background .2s, border-color .2s, color .2s;
    }
    .step-circle.step-active { background: #405189; border-color: #405189; color: #fff; }
    .step-circle.step-done   { background: #0ab39c; border-color: #0ab39c; color: #fff; }

    .wizard-nav-item { cursor: pointer; transition: background .15s; }
    .wizard-nav-item:hover  { background: #f3f6f9; }
    .wizard-nav-item.wiz-active { background: #e8eaf3; }
    .wizard-nav-item.wiz-active .fs-13 { color: #405189; }
    </style>
    @endpush

    @push('scripts')
    <script>
    // ── Wizard ───────────────────────────────────────────────────────────────
    let currentStep = 0;
    const totalSteps = {{ $stepCount }};

    function isStepComplete(stepIdx) {
        const progressEl = document.querySelector(`[data-progress-step="${stepIdx}"]`);
        if (!progressEl) return false;
        const total    = parseInt(progressEl.dataset.total);
        const preDone  = parseInt(progressEl.dataset.predone);
        const newFilled = [...document.querySelectorAll(`.result-select[data-step="${stepIdx}"]`)]
            .filter(s => s.value !== '').length;
        return total > 0 && (preDone + newFilled) >= total;
    }

    function goToStep(n) {
        // Show/hide panels
        document.querySelectorAll('.wizard-panel').forEach(p => p.style.display = 'none');
        document.querySelector(`.wizard-panel[data-step="${n}"]`).style.display = '';

        // Nav item active state
        document.querySelectorAll('.wizard-nav-item').forEach(item => {
            item.classList.toggle('wiz-active', parseInt(item.dataset.step) === n);
        });

        // Step circle states — done (green) takes priority; active (blue) only when not done
        document.querySelectorAll('.step-circle[data-step]').forEach(el => {
            const s    = parseInt(el.dataset.step);
            const done = isStepComplete(s);
            el.classList.toggle('step-done',   done);
            el.classList.toggle('step-active', s === n && !done);
            const numEl   = el.querySelector('.step-num');
            const checkEl = el.querySelector('.step-check');
            if (numEl)   numEl.classList.toggle('d-none', done);
            if (checkEl) checkEl.classList.toggle('d-none', !done);
        });

        currentStep = n;
        updateNavButtons();
        if (n === totalSteps - 1) buildReviewSummary();
    }

    function changeStep(delta) {
        goToStep(Math.min(Math.max(currentStep + delta, 0), totalSteps - 1));
    }

    function updateNavButtons() {
        document.getElementById('prevBtn').disabled = (currentStep === 0);
        const isLast = currentStep === totalSteps - 1;
        document.getElementById('nextBtn').classList.toggle('d-none', isLast);
        document.getElementById('saveBtn').classList.toggle('d-none', !isLast);
    }

    function buildReviewSummary() {
        const rows = document.querySelectorAll('[data-progress-step]');
        let html = '<table class="table table-sm fs-13 mb-0"><thead class="table-light"><tr><th>Asset Type</th><th class="text-center">Recorded</th><th class="text-center">Total</th></tr></thead><tbody>';
        let grandNew = 0;

        rows.forEach(el => {
            const stepIdx  = el.dataset.progressStep;
            const total    = parseInt(el.dataset.total);
            const preDone  = parseInt(el.dataset.predone);
            const label    = el.dataset.typeLabel;
            const newFilled = [...document.querySelectorAll(`.result-select[data-step="${stepIdx}"]`)]
                .filter(s => s.value !== '').length;
            const done = preDone + newFilled;
            grandNew += newFilled;
            const rowCls = done === total ? 'table-success' : '';
            html += `<tr class="${rowCls}">
                <td>${label}</td>
                <td class="text-center fw-medium">${done}</td>
                <td class="text-center text-muted">${total}</td>
            </tr>`;
        });

        const totalAssets = parseInt(document.getElementById('totalDoneCount')?.closest('[data-progress-step]')?.dataset.total ?? 0);
        html += '</tbody></table>';

        if (grandNew === 0 && parseInt(document.getElementById('totalDoneCount').textContent) === 0) {
            html = '<div class="alert alert-warning fs-13"><i class="ri-alert-line me-2"></i>No results have been recorded yet. Go back and inspect assets before saving.</div>' + html;
        }

        document.getElementById('reviewSummary').innerHTML = html;
    }

    // ── Progress tracking ────────────────────────────────────────────────────
    function updateProgress() {
        let grandDone = 0;

        document.querySelectorAll('[data-progress-step]').forEach(el => {
            const stepIdx = el.dataset.progressStep;
            const total   = parseInt(el.dataset.total);
            const preDone = parseInt(el.dataset.predone);

            const newFilled = [...document.querySelectorAll(`.result-select[data-step="${stepIdx}"]`)]
                .filter(s => s.value !== '').length;

            const done = preDone + newFilled;
            el.querySelector('.progress-done-count').textContent = done;
            el.querySelector('.progress-bar').style.width = total ? `${done / total * 100}%` : '0%';

            const navCount = document.querySelector(`.nav-done-count[data-step="${stepIdx}"]`);
            if (navCount) navCount.textContent = done;

            grandDone += done;
        });

        document.getElementById('totalDoneCount').textContent = grandDone;
        document.getElementById('inspectedCount').textContent = grandDone;
    }

    document.querySelectorAll('.result-select').forEach(sel => {
        sel.addEventListener('change', () => {
            updateProgress();
            goToStep(currentStep);
        });
    });

    // ── Expand All per step panel ────────────────────────────────────────────
    document.querySelectorAll('.expand-step-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const panel = this.closest('.wizard-panel');
            const closed = panel.querySelectorAll('.collapse:not(.show)');
            if (closed.length > 0) {
                closed.forEach(c => new bootstrap.Collapse(c, { show: true }));
                this.innerHTML = '<i class="ri-contract-up-down-line me-1"></i>Collapse All';
            } else {
                panel.querySelectorAll('.collapse.show').forEach(c => new bootstrap.Collapse(c, { hide: true }));
                this.innerHTML = '<i class="ri-expand-up-down-line me-1"></i>Expand All';
            }
        });
    });

    // ── Conditional sub-questions ────────────────────────────────────────────
    function onSubTriggerChange(sel) {
        const key    = sel.dataset.asset + '_' + sel.dataset.qid;
        const val    = sel.value;
        const opts   = Array.from(sel.options).map(o => o.value).filter(v => v !== '');
        const yesVal = opts[0] ?? null;
        const noVal  = opts[1] ?? null;

        document.querySelectorAll(`[data-sq-parent="${key}"]`).forEach(div => {
            const cond = div.dataset.sqCond;
            const show = val && ((cond === 'yes' && val === yesVal) || (cond === 'no' && val === noVal));
            div.style.display = show ? '' : 'none';
            div.querySelectorAll('input, select, textarea').forEach(inp => {
                if (show) {
                    if (inp.dataset.wasRequired) { inp.required = true; delete inp.dataset.wasRequired; }
                } else {
                    if (inp.required) { inp.dataset.wasRequired = '1'; inp.required = false; }
                }
            });
        });
    }

    // ── Bulk Fill ────────────────────────────────────────────────────────────
    function updateBulkUI(stepIdx) {
        const cbs        = [...document.querySelectorAll(`.asset-select-cb[data-step="${stepIdx}"]`)];
        const checked    = cbs.filter(cb => cb.checked);
        const count      = checked.length;

        document.querySelectorAll(`.bulk-count-lbl[data-step="${stepIdx}"]`).forEach(el => el.textContent = count);

        const bulkBtn = document.querySelector(`.bulk-fill-btn[data-step="${stepIdx}"]`);
        if (bulkBtn) bulkBtn.classList.toggle('d-none', count === 0);

        const selAll = document.getElementById(`selectAll_${stepIdx}`);
        if (selAll) {
            selAll.indeterminate = count > 0 && count < cbs.length;
            selAll.checked       = count > 0 && count === cbs.length;
        }
    }

    function toggleBulkPanel(stepIdx) {
        document.getElementById(`bulkPanel_${stepIdx}`)?.classList.toggle('d-none');
    }

    function applyBulk(stepIdx) {
        const bulkResult = document.getElementById(`bulkResult_${stepIdx}`)?.value ?? '';
        const bulkRec    = document.getElementById(`bulkRec_${stepIdx}`)?.value    ?? '';
        const bulkAction = document.getElementById(`bulkAction_${stepIdx}`)?.value ?? '';
        const bulkQInputs = [...document.querySelectorAll(`#bulkPanel_${stepIdx} .bulk-q-input`)];

        const checked = [...document.querySelectorAll(`.asset-select-cb[data-step="${stepIdx}"]:checked`)];

        checked.forEach(cb => {
            const assetId = cb.dataset.asset;

            // Open the asset's collapse so inputs are active
            const collapse = document.getElementById(`asset_${assetId}`);
            if (collapse && !collapse.classList.contains('show')) {
                new bootstrap.Collapse(collapse, { show: true });
            }

            // Set result
            const resultSel = document.querySelector(`select[name="assets[${assetId}][result]"]`);
            if (resultSel && bulkResult) {
                resultSel.value = bulkResult;
                resultSel.dispatchEvent(new Event('change'));
            }

            // Set recommendation & required action
            const recField    = document.querySelector(`textarea[name="assets[${assetId}][recommendation]"]`);
            const actionField = document.querySelector(`textarea[name="assets[${assetId}][required_action]"]`);
            if (recField    && bulkRec)    recField.value    = bulkRec;
            if (actionField && bulkAction) actionField.value = bulkAction;

            // Set questionnaire answers
            bulkQInputs.forEach(bqInput => {
                if (!bqInput.value) return;
                const target = document.querySelector(`[name="answers[${assetId}][${bqInput.dataset.qid}]"]`);
                if (target) target.value = bqInput.value;
            });

            cb.checked = false;
        });

        updateBulkUI(stepIdx);
        updateProgress();
        goToStep(currentStep);

        // Hide & reset bulk panel
        document.getElementById(`bulkPanel_${stepIdx}`)?.classList.add('d-none');
        document.getElementById(`bulkResult_${stepIdx}`).value = '';
        document.getElementById(`bulkRec_${stepIdx}`).value    = '';
        document.getElementById(`bulkAction_${stepIdx}`).value = '';
        bulkQInputs.forEach(inp => inp.value = '');
    }

    document.addEventListener('change', function (e) {
        if (e.target.matches('.asset-select-cb')) {
            updateBulkUI(e.target.dataset.step);
        }
        if (e.target.matches('.select-all-cb')) {
            const stepIdx = e.target.dataset.step;
            document.querySelectorAll(`.asset-select-cb[data-step="${stepIdx}"]`)
                .forEach(cb => cb.checked = e.target.checked);
            updateBulkUI(stepIdx);
        }
    });

    // ── Save Draft ───────────────────────────────────────────────────────────
    function saveDraft() {
        document.getElementById('saveDraftInput').value = '1';
        document.getElementById('inspectForm').submit();
    }

    // ── Init ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        // Strip required from initially-hidden conditional sub-question inputs
        document.querySelectorAll('[data-sq-cond]').forEach(div => {
            if (div.style.display === 'none') {
                div.querySelectorAll('input, select, textarea').forEach(inp => {
                    if (inp.required) { inp.dataset.wasRequired = '1'; inp.required = false; }
                });
            }
        });

        // Auto-expand on validation failure (old() values present)
        document.querySelectorAll('.result-select').forEach(sel => {
            if (sel.value !== '') {
                const c = sel.closest('.collapse');
                if (c) new bootstrap.Collapse(c, { show: true });
            }
        });

        goToStep(0);
        updateProgress();
    });
    </script>
    @endpush
</x-app-layout>
