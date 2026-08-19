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

    @if($errors->any())
    <div class="alert alert-danger alert-border-left alert-dismissible fade show">
        <i class="ri-error-warning-line me-2"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('technician.jobs.inspect.store', $job) }}" id="inspectForm">
        @csrf

        {{-- Inspection date --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-end g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Inspection Date <span class="text-danger">*</span></label>
                        <input type="date" name="inspection_date" class="form-control @error('inspection_date') is-invalid @enderror"
                               value="{{ old('inspection_date', date('Y-m-d')) }}" required>
                        @error('inspection_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-muted fs-13">
                                <span id="inspectedCount" class="fw-semibold text-primary">0</span> asset(s) marked for inspection
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="expandAllBtn">
                                <i class="ri-expand-up-down-line me-1"></i>Expand All
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Assets grouped by building --}}
        @forelse($grouped as $buildingName => $assets)
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="ri-building-line me-2 text-primary"></i>{{ $buildingName }}
                    <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $assets->count() }}</span>
                </h6>
            </div>
            <div class="card-body p-0">
                @foreach($assets as $asset)
                @php
                    $isDone      = $doneAssetIds->contains($asset->id);
                    $isTarget    = $targetAssetIds->contains($asset->id);
                    $prevResult  = $asset->currentInspection?->result;
                    $prevColor   = $resultColors[$prevResult] ?? 'secondary';
                    $collapseId  = 'asset_' . $asset->id;
                    $assetQs     = $questionsByType->get($asset->asset_type, collect());
                @endphp

                <div class="border-bottom asset-row {{ $isDone ? 'bg-success-subtle' : '' }}"
                     data-asset-id="{{ $asset->id }}">

                    {{-- Asset header --}}
                    <div class="d-flex align-items-center px-3 py-2 gap-2">
                        <div class="flex-grow-1">
                            <span class="fw-medium fs-13">{{ $asset->asset_code }}</span>
                            <span class="text-muted fs-12 ms-2">{{ $assetTypes[$asset->asset_type] ?? $asset->asset_type }}</span>
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
                            <span class="badge bg-success-subtle text-success"><i class="ri-checkbox-circle-line me-1"></i>Recorded</span>
                            @else
                            <button type="button" class="btn btn-sm btn-outline-primary toggle-inspect"
                                    data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}">
                                <i class="ri-add-line me-1"></i>Inspect
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Collapsible inspection fields --}}
                    @if(!$isDone)
                    <div class="collapse" id="{{ $collapseId }}">
                        <div class="px-3 pb-3 pt-1 bg-light bg-opacity-50">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label fs-12 text-muted mb-1">Result <span class="text-danger">*</span></label>
                                    <select name="assets[{{ $asset->id }}][result]"
                                            class="form-select form-select-sm result-select"
                                            data-asset-id="{{ $asset->id }}">
                                        <option value="">— select —</option>
                                        @foreach(\App\Models\InspectionRecord::RESULTS as $r)
                                        <option value="{{ $r }}" {{ old("assets.{$asset->id}.result") == $r ? 'selected' : '' }}>
                                            {{ ucwords(str_replace('_', ' ', $r)) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label fs-12 text-muted mb-1">Condition</label>
                                    <input type="text" name="assets[{{ $asset->id }}][condition]"
                                           class="form-control form-control-sm"
                                           placeholder="General condition notes…"
                                           value="{{ old("assets.{$asset->id}.condition") }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-12 text-muted mb-1">Defect Description</label>
                                    <textarea name="assets[{{ $asset->id }}][defect_description]"
                                              class="form-control form-control-sm" rows="2"
                                              placeholder="Describe any defects…">{{ old("assets.{$asset->id}.defect_description") }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-12 text-muted mb-1">Reason for Result</label>
                                    <textarea name="assets[{{ $asset->id }}][reason_for_result]"
                                              class="form-control form-control-sm" rows="2"
                                              placeholder="Reason…">{{ old("assets.{$asset->id}.reason_for_result") }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-12 text-muted mb-1">Recommendation</label>
                                    <textarea name="assets[{{ $asset->id }}][recommendation]"
                                              class="form-control form-control-sm" rows="2"
                                              placeholder="Recommendation…">{{ old("assets.{$asset->id}.recommendation") }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-12 text-muted mb-1">Required Action</label>
                                    <textarea name="assets[{{ $asset->id }}][required_action]"
                                              class="form-control form-control-sm" rows="2"
                                              placeholder="Action required…">{{ old("assets.{$asset->id}.required_action") }}</textarea>
                                </div>
                            </div>

                            {{-- Checklist questions for this asset type --}}
                            @if($assetQs->isNotEmpty())
                            <hr class="my-2">
                            <p class="fs-11 text-uppercase text-muted fw-semibold mb-2">
                                <i class="ri-list-check-3 me-1"></i>Inspection Checklist
                            </p>
                            <div class="row g-2">
                                @foreach($assetQs as $q)
                                @php
                                    $fieldType = $q->fieldType;
                                    $inputName = "answers[{$asset->id}][{$q->id}]";
                                    $oldVal    = old("answers.{$asset->id}.{$q->id}");
                                @endphp
                                <div class="col-md-6">
                                    <label class="form-label fs-12 text-muted mb-1">
                                        {{ $q->name }}
                                        @if($q->required)<span class="text-danger">*</span>@endif
                                    </label>
                                    @if($q->type === 'long_text')
                                        <textarea name="{{ $inputName }}" class="form-control form-control-sm" rows="2"
                                                  @if($q->required) required @endif>{{ $oldVal }}</textarea>
                                    @elseif(in_array($q->type, ['switch', 'option_list']) && $fieldType)
                                        <select name="{{ $inputName }}" class="form-select form-select-sm"
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

                                    {{-- Sub-questions (shown inline below parent) --}}
                                    @foreach($q->subQuestionnaires as $sq)
                                    @php
                                        $sqName   = "answers[{$asset->id}][{$sq->id}]";
                                        $sqOldVal = old("answers.{$asset->id}.{$sq->id}");
                                        $sqFt     = $sq->fieldType;
                                    @endphp
                                    <div class="mt-1 ps-2 border-start border-2 border-secondary-subtle">
                                        <label class="form-label fs-11 text-muted mb-1">
                                            ↳ {{ $sq->name }}
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
                            @endif

                        </div>
                    </div>
                    @endif

                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="ri-tools-line fs-24 d-block mb-2"></i>
                No assets found at this job's site. Ensure assets have been registered for this site.
            </div>
        </div>
        @endforelse

        @if($grouped->isNotEmpty())
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="ri-save-line me-1"></i>Save Inspection Records
            </button>
            <a href="{{ route('technician.jobs.show', $job) }}" class="btn btn-light">Cancel</a>
        </div>
        @endif
    </form>

    @push('scripts')
    <script>
    (function () {
        const resultSelects = document.querySelectorAll('.result-select');
        const countEl = document.getElementById('inspectedCount');

        function updateCount() {
            const filled = [...resultSelects].filter(s => s.value !== '').length;
            countEl.textContent = filled;
        }

        resultSelects.forEach(sel => sel.addEventListener('change', updateCount));

        // Expand / collapse toggle button
        document.getElementById('expandAllBtn')?.addEventListener('click', function () {
            const collapses = document.querySelectorAll('.collapse:not(.show)');
            if (collapses.length > 0) {
                collapses.forEach(c => new bootstrap.Collapse(c, { show: true }));
                this.innerHTML = '<i class="ri-contract-up-down-line me-1"></i>Collapse All';
            } else {
                document.querySelectorAll('.collapse.show').forEach(c => new bootstrap.Collapse(c, { hide: true }));
                this.innerHTML = '<i class="ri-expand-up-down-line me-1"></i>Expand All';
            }
        });

        // Auto-expand rows that have old() values (validation failure)
        resultSelects.forEach(sel => {
            if (sel.value !== '') {
                const collapseEl = sel.closest('.collapse');
                if (collapseEl) new bootstrap.Collapse(collapseEl, { show: true });
            }
        });

        updateCount();
    })();
    </script>
    @endpush
</x-app-layout>
