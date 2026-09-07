<x-app-layout>
    <x-slot name="title">Register & Inspect Assets</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Register & Inspect Assets</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('technician.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('technician.jobs.index') }}">My Jobs</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('technician.jobs.show', $job) }}">Job</a></li>
                        <li class="breadcrumb-item active">Register & Inspect</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Job summary --}}
    <div class="alert alert-primary alert-border-left mb-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div>
                <span class="fw-semibold">{{ $job->client->name ?? '—' }}</span>
                <span class="text-muted mx-1">·</span>
                {{ $job->site->name ?? $job->site->address }}
            </div>
            <span class="badge bg-info-subtle text-info">{{ \App\Models\Job::WORK_TYPES[$job->work_type] }}</span>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-border-left alert-dismissible fade show">
        <i class="ri-error-warning-line me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('technician.jobs.register-inspect.store', $job) }}"
          enctype="multipart/form-data" id="riForm">
        @csrf
        <input type="hidden" name="mode" id="modeInput" value="{{ old('mode', 'single') }}">

        <div class="row g-3">

            {{-- ── Asset Identity ──────────────────────────────────────────────── --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0">
                            <i class="ri-barcode-line me-2 text-primary"></i>Asset Identity
                        </h6>
                        <div class="btn-group btn-group-sm">
                            <input type="radio" class="btn-check" name="mode_ui" id="modeSingle" autocomplete="off"
                                   {{ old('mode', 'single') === 'single' ? 'checked' : '' }}
                                   onchange="setMode('single')">
                            <label class="btn btn-outline-primary" for="modeSingle">
                                <i class="ri-file-line me-1"></i>Single
                            </label>
                            <input type="radio" class="btn-check" name="mode_ui" id="modeRange" autocomplete="off"
                                   {{ old('mode') === 'range' ? 'checked' : '' }}
                                   onchange="setMode('range')">
                            <label class="btn btn-outline-primary" for="modeRange">
                                <i class="ri-list-check me-1"></i>Range
                            </label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">

                            {{-- Asset Type --}}
                            <div class="col-md-4">
                                <label class="form-label">Asset Type <span class="text-danger">*</span></label>
                                <select name="asset_type" id="assetTypeSelect"
                                        class="form-select @error('asset_type') is-invalid @enderror"
                                        required onchange="onAssetTypeChange(this.value)">
                                    <option value="">— Select Type —</option>
                                    @foreach($assetTypes as $val => $label)
                                    <option value="{{ $val }}" data-code="{{ $val }}"
                                            {{ old('asset_type') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('asset_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Single: asset_code with prefix badge --}}
                            <div class="col-md-4 single-only">
                                <label class="form-label">Asset Code <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted font-monospace" id="singlePrefixBadge" style="display:none"></span>
                                    <input type="text" name="asset_code" id="singleAssetCode"
                                           class="form-control @error('asset_code') is-invalid @enderror"
                                           value="{{ old('asset_code') }}" placeholder="e.g. 01" maxlength="100"
                                           oninput="checkSingleCode()">
                                </div>
                                <div id="singleCodeIndicator" class="fs-12 mt-1"></div>
                                @error('asset_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            {{-- Range: auto-prefix display --}}
                            <div class="col-12 range-only">
                                <label class="form-label text-muted fs-12 mb-1">Auto-generated Prefix</label>
                                <div id="rangePrefixDisplay" class="input-group-text bg-light text-muted font-monospace fs-13 d-inline-block px-3 py-2 rounded border">—</div>
                                @error('asset_code')
                                <div class="text-danger fs-12 mt-1"><i class="ri-error-warning-line me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Range: start / qty / end (read-only) / indicator --}}
                            <div class="col-md-2 range-only">
                                <label class="form-label">Start <span class="text-danger">*</span></label>
                                <input type="text" name="range_start" id="rangeStart" inputmode="numeric"
                                       class="form-control @error('range_start') is-invalid @enderror"
                                       value="{{ old('range_start') }}" placeholder="01"
                                       oninput="recalcEnd()">
                                @error('range_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2 range-only">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="rangeQty"
                                       class="form-control @error('quantity') is-invalid @enderror"
                                       value="{{ old('quantity') }}" placeholder="6" min="1" max="200"
                                       oninput="recalcEnd()">
                                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2 range-only">
                                <label class="form-label">End</label>
                                <input type="text" name="range_end" id="rangeEnd"
                                       class="form-control bg-light @error('range_end') is-invalid @enderror"
                                       value="{{ old('range_end') }}" placeholder="—" readonly>
                                @error('range_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col range-only d-flex align-items-end pb-1">
                                <div id="rangeIndicator" class="fs-13 lh-sm"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Location & Equipment ─────────────────────────────────────────── --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ri-map-pin-line me-2 text-secondary"></i>Location & Equipment
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Building <span class="text-danger">*</span></label>
                                <select name="building_id" id="buildingSelect"
                                        class="form-select @error('building_id') is-invalid @enderror"
                                        onchange="onBuildingChange(this)" required>
                                    <option value="" disabled>— Select Building —</option>
                                    @foreach($buildings as $b)
                                    @php
                                        $autoSelected = old('building_id')
                                            ? old('building_id') === $b->id
                                            : $buildings->count() === 1;
                                    @endphp
                                    <option value="{{ $b->id }}" data-building-code="{{ $b->building_code }}"
                                            {{ $autoSelected ? 'selected' : '' }}>
                                        {{ $b->name_or_level }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('building_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Zone / Area</label>
                                <input type="text" name="zone" class="form-control @error('zone') is-invalid @enderror"
                                       value="{{ old('zone') }}" placeholder="e.g. Level 2, Zone A" maxlength="255">
                                @error('zone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Make</label>
                                <input type="text" name="make" class="form-control"
                                       value="{{ old('make') }}" maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Model</label>
                                <input type="text" name="model" class="form-control"
                                       value="{{ old('model') }}" maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Serial / Batch No.</label>
                                <input type="text" name="serial_or_batch" class="form-control"
                                       value="{{ old('serial_or_batch') }}" maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rating</label>
                                <input type="text" name="rating" class="form-control"
                                       value="{{ old('rating') }}" maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Install Date</label>
                                <input type="date" name="install_date" class="form-control"
                                       value="{{ old('install_date') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Inspection Details ───────────────────────────────────────────── --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ri-survey-line me-2 text-primary"></i>Inspection Details
                            <small class="text-muted fw-normal ms-1 range-only" style="display:none;">(applies to all assets in range)</small>
                        </h6>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        <div>
                            <label class="form-label">Inspection Date <span class="text-danger">*</span></label>
                            <input type="date" name="inspection_date"
                                   class="form-control @error('inspection_date') is-invalid @enderror"
                                   value="{{ old('inspection_date', date('Y-m-d')) }}" required>
                            @error('inspection_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">Result <span class="text-danger">*</span></label>
                            <select name="result" class="form-select @error('result') is-invalid @enderror" required>
                                <option value="">— Select Result —</option>
                                @foreach(\App\Models\InspectionRecord::RESULTS as $r)
                                <option value="{{ $r }}" {{ old('result') === $r ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $r)) }}
                                </option>
                                @endforeach
                            </select>
                            @error('result')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">Recommendation</label>
                            <textarea name="recommendation" class="form-control" rows="2">{{ old('recommendation') }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">Required Action</label>
                            <textarea name="required_action" class="form-control" rows="2">{{ old('required_action') }}</textarea>
                        </div>
                        <div>
                            <label class="form-label"><i class="ri-camera-line me-1"></i>Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Inspection Checklist (shown when asset type is selected) ─────── --}}
            <div class="col-12" id="checklistCard" style="display:none;">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ri-list-check-3 me-2 text-success"></i>Inspection Checklist
                            <small class="text-muted fw-normal ms-1 range-only" style="display:none;">(answers apply to all assets in range)</small>
                        </h6>
                    </div>
                    <div class="card-body">
                        <p id="noChecklistMsg" class="text-muted fs-13 mb-0" style="display:none;">
                            <i class="ri-information-line me-1"></i>No checklist configured for this asset type.
                        </p>
                        <div id="checklistBody" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Already registered in this job --}}
        @if($registeredThisJob->isNotEmpty())
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h6 class="card-title mb-0 flex-grow-1">
                        <i class="ri-history-line me-2 text-muted"></i>Already Registered This Job
                    </h6>
                    <span class="badge bg-secondary-subtle text-secondary">{{ $registeredThisJob->count() }} asset(s)</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Asset Code</th>
                                <th>Type</th>
                                <th>Building</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registeredThisJob as $ra)
                            @php
                                $result = $ra->currentInspection?->result;
                                $rc = match($result) {
                                    'pass'           => 'success',
                                    'fail'           => 'danger',
                                    'restricted_use' => 'warning',
                                    'under_review'   => 'warning',
                                    default          => 'secondary',
                                };
                            @endphp
                            <tr>
                                <td class="ps-3 fw-semibold fs-13 font-monospace">{{ $ra->asset_code }}</td>
                                <td class="fs-12 text-muted">{{ $assetTypes[$ra->asset_type] ?? $ra->asset_type }}</td>
                                <td class="fs-12 text-muted">{{ $ra->building?->name_or_level ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $rc }}-subtle text-{{ $rc }} text-capitalize">
                                        {{ $result ? ucwords(str_replace('_', ' ', $result)) : 'No record' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Footer --}}
        <div class="d-flex gap-2 justify-content-end mt-3 mb-4">
            <a href="{{ route('technician.jobs.show', $job) }}" class="btn btn-light">
                <i class="ri-arrow-left-line me-1"></i>Cancel
            </a>
            <button type="submit" id="submitBtn" class="btn btn-primary">
                <i class="ri-save-line me-1"></i><span id="submitLabel">Register & Submit Inspection</span>
            </button>
        </div>
    </form>

    {{-- Questionnaire templates (Blade-rendered, hidden, cloned by JS) --}}
    <div id="qTemplates" style="display:none" aria-hidden="true">
        @foreach($questionsByType as $assetType => $questions)
        <div class="q-tpl" data-asset-type="{{ $assetType }}">
            @foreach($questions as $q)
            @php
                $fieldType = $q->fieldType;
                $hasConditionalSubs = in_array($q->type, ['switch', 'three_tier_switch'])
                    && $q->subQuestionnaires->whereNotNull('condition')->isNotEmpty();
                $oldVal = old("answers.{$q->id}");
                $qNum   = $loop->iteration;
            @endphp
            <div class="border rounded p-2 bg-white">
                <label class="form-label fs-12 fw-medium mb-1 d-flex align-items-center gap-1">
                    <span class="badge bg-secondary-subtle text-secondary fw-semibold" style="min-width:20px">{{ $qNum }}</span>
                    {{ $q->name }}@if($q->required)<span class="text-danger ms-1">*</span>@endif
                </label>

                @if($q->type === 'long_text')
                    <textarea name="answers[{{ $q->id }}]" class="form-control form-control-sm" rows="2"
                              @if($q->required) required @endif>{{ $oldVal }}</textarea>
                @elseif(in_array($q->type, ['switch', 'three_tier_switch', 'option_list']) && $fieldType)
                    <select name="answers[{{ $q->id }}]" class="form-select form-select-sm"
                            @if($hasConditionalSubs) data-qid="{{ $q->id }}" onchange="riSubTrigger(this)" @endif
                            @if($q->required) required @endif>
                        <option value="">— select —</option>
                        @foreach($fieldType->options ?? [] as $opt)
                        <option value="{{ $opt }}" {{ $oldVal === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                @elseif($q->type === 'numeric')
                    <input type="number" step="any" name="answers[{{ $q->id }}]"
                           class="form-control form-control-sm" value="{{ $oldVal }}"
                           @if($q->required) required @endif>
                @elseif($q->type === 'date')
                    <input type="date" name="answers[{{ $q->id }}]"
                           class="form-control form-control-sm" value="{{ $oldVal }}"
                           @if($q->required) required @endif>
                @else
                    <input type="text" name="answers[{{ $q->id }}]"
                           class="form-control form-control-sm" value="{{ $oldVal }}"
                           @if($q->required) required @endif>
                @endif

                @php $sqCounters = []; @endphp
                @foreach($q->subQuestionnaires as $sq)
                @php
                    $sqFt      = $sq->fieldType;
                    $sqOldVal  = old("answers.{$sq->id}");
                    $sqCondKey = $sq->condition ?? '__none__';
                    $sqCounters[$sqCondKey] = $sqCounters[$sqCondKey] ?? 0;
                    $sqLetter  = chr(ord('a') + $sqCounters[$sqCondKey]++);
                @endphp
                <div class="mt-2 ps-2 border-start border-2 border-secondary-subtle"
                     @if($sq->condition) data-sq-parent="{{ $q->id }}" data-sq-cond="{{ $sq->condition }}" style="display:none;" @endif>
                    <label class="form-label fs-11 text-muted mb-1 d-flex align-items-center gap-1">
                        <span class="badge bg-light text-secondary border fw-semibold" style="min-width:18px;font-size:10px">{{ $sqLetter }}</span>
                        {{ $sq->name }}@if($sq->required)<span class="text-danger ms-1">*</span>@endif
                    </label>
                    @if($sq->type === 'long_text')
                        <textarea name="answers[{{ $sq->id }}]" class="form-control form-control-sm" rows="1"
                                  @if($sq->required) required @endif>{{ $sqOldVal }}</textarea>
                    @elseif(in_array($sq->type, ['switch', 'three_tier_switch', 'option_list']) && $sqFt)
                        <select name="answers[{{ $sq->id }}]" class="form-select form-select-sm"
                                @if($sq->required) required @endif>
                            <option value="">— select —</option>
                            @foreach($sqFt->options ?? [] as $opt)
                            <option value="{{ $opt }}" {{ $sqOldVal === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    @elseif($sq->type === 'numeric')
                        <input type="number" step="any" name="answers[{{ $sq->id }}]"
                               class="form-control form-control-sm" value="{{ $sqOldVal }}"
                               @if($sq->required) required @endif>
                    @elseif($sq->type === 'date')
                        <input type="date" name="answers[{{ $sq->id }}]"
                               class="form-control form-control-sm" value="{{ $sqOldVal }}"
                               @if($sq->required) required @endif>
                    @else
                        <input type="text" name="answers[{{ $sq->id }}]"
                               class="form-control form-control-sm" value="{{ $sqOldVal }}"
                               @if($sq->required) required @endif>
                    @endif
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
        @endforeach
    </div>

    @push('scripts')
    <script>
    const CLIENT_CODE       = @json($clientCode);
    const CHECK_CODES_URL   = @json(route('technician.jobs.check-asset-codes', $job));
    const CSRF_TOKEN        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let currentBuildingCode  = '';
    let currentAssetTypeCode = '';
    let checkDebounceTimer   = null;

    function buildAutoPrefix() {
        const locParts = [CLIENT_CODE, currentBuildingCode].filter(Boolean);
        const locStr   = locParts.length ? locParts.join('-') + '-' : '';
        return locStr + (currentAssetTypeCode || '');
    }

    function updatePrefixBadges() {
        const display = buildAutoPrefix();

        const badge = document.getElementById('singlePrefixBadge');
        if (badge) {
            if (display) { badge.textContent = display; badge.style.display = ''; }
            else { badge.style.display = 'none'; }
        }

        const rangePfx = document.getElementById('rangePrefixDisplay');
        if (rangePfx) rangePfx.textContent = display || '—';
    }

    function onBuildingChange(sel) {
        const opt = sel.options[sel.selectedIndex];
        currentBuildingCode = opt?.dataset.buildingCode || '';
        updatePrefixBadges();
        recalcEnd();
        checkSingleCode();
    }

    // ── Mode toggle ──────────────────────────────────────────────────────────────
    function setMode(mode) {
        document.getElementById('modeInput').value = mode;
        document.querySelectorAll('.single-only').forEach(el => el.style.display = mode === 'single' ? '' : 'none');
        document.querySelectorAll('.range-only').forEach(el => el.style.display = mode === 'range' ? '' : 'none');

        if (mode === 'range') {
            const ac = document.querySelector('[name="asset_code"]');
            if (ac) ac.value = '';
            recalcEnd();
        } else {
            ['rangeStart', 'rangeEnd', 'rangeQty'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            document.getElementById('rangeIndicator').innerHTML = '';
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('submitLabel').textContent = 'Register & Submit Inspection';
        }
    }

    // ── Range: Start + Qty → auto-calculate End ───────────────────────────────────
    function recalcEnd() {
        if (document.getElementById('modeInput').value !== 'range') return;

        const startRaw  = document.getElementById('rangeStart').value.trim();
        const qty       = parseInt(document.getElementById('rangeQty').value, 10);
        const start     = parseInt(startRaw, 10);
        const endInput  = document.getElementById('rangeEnd');
        const indicator = document.getElementById('rangeIndicator');
        const btn       = document.getElementById('submitBtn');
        const label     = document.getElementById('submitLabel');

        if (isNaN(start) || isNaN(qty) || qty < 1) {
            endInput.value = '';
            indicator.innerHTML = '';
            btn.disabled = true;
            clearTimeout(checkDebounceTimer);
            return;
        }

        const end    = start + qty - 1;
        const padLen = Math.max(startRaw.length, String(end).length);
        const pad    = n => String(n).padStart(padLen, '0');
        endInput.value = pad(end);

        const fullPrefix = buildAutoPrefix();
        const codes = [];
        for (let i = start; i <= end; i++) codes.push(fullPrefix + pad(i));

        const first = codes[0];
        const last  = codes[codes.length - 1];

        indicator.innerHTML = `<span class="text-muted"><i class="ri-loader-4-line me-1"></i>Checking…</span>`;
        label.textContent = `Register & Inspect ${qty} Asset${qty > 1 ? 's' : ''}`;
        btn.disabled = true;

        clearTimeout(checkDebounceTimer);
        checkDebounceTimer = setTimeout(() => {
            const params = new URLSearchParams();
            codes.forEach(c => params.append('codes[]', c));

            fetch(`${CHECK_CODES_URL}?${params.toString()}`, {
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.existing && data.existing.length > 0) {
                    const list = data.existing.join(', ');
                    indicator.innerHTML = `<span class="text-danger"><i class="ri-error-warning-line me-1"></i>Already exist: <strong>${list}</strong></span>`;
                    btn.disabled = true;
                } else {
                    indicator.innerHTML = `<span class="text-success"><i class="ri-check-line me-1"></i>${first} → ${last}</span>`;
                    btn.disabled = false;
                }
            })
            .catch(() => {
                indicator.innerHTML = `<span class="text-success"><i class="ri-check-line me-1"></i>${first} → ${last}</span>`;
                btn.disabled = false;
            });
        }, 400);
    }

    // ── Single code live check ────────────────────────────────────────────────────
    let singleCheckTimer = null;

    function checkSingleCode() {
        const input     = document.getElementById('singleAssetCode');
        const indicator = document.getElementById('singleCodeIndicator');
        const btn       = document.getElementById('submitBtn');
        const suffix    = input.value.trim();

        if (!suffix) {
            indicator.innerHTML = '';
            btn.disabled = false;
            clearTimeout(singleCheckTimer);
            return;
        }

        const fullCode = buildAutoPrefix() + suffix;
        indicator.innerHTML = `<span class="text-muted"><i class="ri-loader-4-line me-1"></i>Checking…</span>`;
        btn.disabled = true;

        clearTimeout(singleCheckTimer);
        singleCheckTimer = setTimeout(() => {
            const params = new URLSearchParams({ 'codes[]': fullCode });
            fetch(`${CHECK_CODES_URL}?${params.toString()}`, {
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.existing && data.existing.length > 0) {
                    indicator.innerHTML = `<span class="text-danger"><i class="ri-error-warning-line me-1"></i><strong>${fullCode}</strong> already exists at this site.</span>`;
                    btn.disabled = true;
                } else {
                    indicator.innerHTML = `<span class="text-success"><i class="ri-check-line me-1"></i>${fullCode} is available.</span>`;
                    btn.disabled = false;
                }
            })
            .catch(() => {
                indicator.innerHTML = '';
                btn.disabled = false;
            });
        }, 400);
    }

    // ── Questionnaire ─────────────────────────────────────────────────────────────
    function onAssetTypeChange(assetType) {
        const sel = document.getElementById('assetTypeSelect');
        const opt = sel.options[sel.selectedIndex];
        currentAssetTypeCode = opt?.dataset.code || '';
        updatePrefixBadges();
        recalcEnd();
        checkSingleCode();

        const card  = document.getElementById('checklistCard');
        const noMsg = document.getElementById('noChecklistMsg');
        const body  = document.getElementById('checklistBody');
        body.innerHTML = '';
        if (!assetType) { card.style.display = 'none'; return; }
        const tpl = document.querySelector(`#qTemplates .q-tpl[data-asset-type="${assetType}"]`);
        card.style.display = '';
        if (!tpl || tpl.children.length === 0) { noMsg.style.display = ''; return; }
        noMsg.style.display = 'none';
        body.innerHTML = tpl.innerHTML;
    }

    function riSubTrigger(sel) {
        const qId    = sel.dataset.qid;
        const chosen = sel.value;
        const opts   = Array.from(sel.options).map(o => o.value).filter(v => v);
        const condMap = { yes: opts[0], no: opts[1], opt1: opts[0], opt2: opts[1], opt3: opts[2] };
        document.querySelectorAll(`[data-sq-parent="${qId}"]`).forEach(el => {
            const visible = condMap[el.dataset.sqCond] === chosen;
            el.style.display = visible ? '' : 'none';
            el.querySelectorAll('input, select, textarea').forEach(inp => {
                if (visible) {
                    if (inp.dataset.wasRequired) inp.required = true;
                } else {
                    inp.dataset.wasRequired = inp.required ? '1' : '';
                    inp.required = false;
                }
            });
        });
    }

    // ── Init ──────────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        // Strip required from initially-hidden sub-question inputs so HTML5 validation doesn't block submit
        document.querySelectorAll('[data-sq-parent][style*="display:none"], [data-sq-parent][style*="display: none"]').forEach(el => {
            el.querySelectorAll('input, select, textarea').forEach(inp => {
                inp.dataset.wasRequired = inp.required ? '1' : '';
                inp.required = false;
            });
        });

        // Restore building code if pre-selected (validation failure re-render)
        const bSel = document.getElementById('buildingSelect');
        if (bSel) {
            const bOpt = bSel.options[bSel.selectedIndex];
            currentBuildingCode = bOpt?.dataset.buildingCode || '';
        }
        // Restore asset type code
        const atSel = document.getElementById('assetTypeSelect');
        const atOpt = atSel?.options[atSel.selectedIndex];
        currentAssetTypeCode = atOpt?.dataset.code || '';

        updatePrefixBadges();
        setMode(document.getElementById('modeInput').value);
        if (atSel?.value) onAssetTypeChange(atSel.value);
    });
    </script>
    @endpush
</x-app-layout>
