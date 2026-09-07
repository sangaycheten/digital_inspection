<x-app-layout>
    <x-slot name="title">Add Asset</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Add Asset</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.assets.index') }}">Asset Register</a></li>
                        <li class="breadcrumb-item active">Add Asset</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.assets.store') }}" id="assetForm">
        @csrf
        <input type="hidden" name="mode" id="modeInput" value="{{ old('mode', 'single') }}">

        <div class="row g-3">

            {{-- Left column --}}
            <div class="col-lg-8">

                {{-- Location --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-map-pin-line me-2 text-primary"></i>Location</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Site <span class="text-danger">*</span></label>
                                <select name="site_id" id="siteSelect"
                                        class="form-select @error('site_id') is-invalid @enderror" required>
                                    <option value="">— Select Site —</option>
                                    @foreach($sites as $site)
                                    <option value="{{ $site->id }}"
                                        data-client-code="{{ $site->client->custom_client_code ?? '' }}"
                                        {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                        {{ $site->name ?? $site->address }}
                                        @if($site->client) ({{ $site->client->name }}) @endif
                                    </option>
                                    @endforeach
                                </select>
                                @error('site_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Building</label>
                                <select name="building_id" id="buildingSelect"
                                        class="form-select @error('building_id') is-invalid @enderror">
                                    <option value="">— Select Building —</option>
                                </select>
                                @error('building_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Zone / Area</label>
                                <input type="text" name="zone"
                                       class="form-control @error('zone') is-invalid @enderror"
                                       value="{{ old('zone') }}" placeholder="e.g. Rooftop North">
                                @error('zone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Asset Identity --}}
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0"><i class="ri-barcode-line me-2 text-primary"></i>Asset Identity</h6>
                        <div class="btn-group btn-group-sm" role="group">
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
                        <div class="row g-3">

                            {{-- Asset Type (both modes) — must come first so its code feeds the prefix --}}
                            <div class="col-md-4">
                                <label class="form-label">Asset Type <span class="text-danger">*</span></label>
                                <select name="asset_type" id="assetTypeSelect"
                                        class="form-select @error('asset_type') is-invalid @enderror" required
                                        onchange="onAssetTypeChange()">
                                    <option value="">— Select Type —</option>
                                    @foreach($assetTypes as $val => $label)
                                    <option value="{{ $val }}" data-code="{{ $val }}"
                                            {{ old('asset_type') == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('asset_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Single mode: asset_code --}}
                            <div class="col-md-4 single-only">
                                <label class="form-label">Asset Code <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted" id="singleClientCodeBadge" style="display:none"></span>
                                    <input type="text" name="asset_code"
                                           class="form-control @error('asset_code') is-invalid @enderror"
                                           value="{{ old('asset_code') }}" placeholder="e.g. 01">
                                </div>
                                <div class="form-text">Must be unique within the selected site.</div>
                                @error('asset_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Range mode: auto-prefix display --}}
                            <div class="col-12 range-only">
                                <label class="form-label text-muted fs-12 mb-1">Auto-generated Prefix</label>
                                <div id="rangePrefixDisplay" class="input-group-text bg-light text-muted font-monospace fs-13 d-inline-block px-3 py-2 rounded border">
                                    —
                                </div>
                            </div>

                            {{-- Range mode: start / end / quantity / indicator --}}
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
                                <input type="number" name="quantity" id="quantity"
                                       class="form-control @error('quantity') is-invalid @enderror"
                                       value="{{ old('quantity') }}" placeholder="6" min="1"
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
                            <div class="col-md-3 range-only d-flex align-items-end pb-1">
                                <div id="rangeIndicator" class="fs-13 lh-sm"></div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Equipment Details --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-settings-2-line me-2 text-primary"></i>Equipment Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Make</label>
                                <input type="text" name="make"
                                       class="form-control @error('make') is-invalid @enderror"
                                       value="{{ old('make') }}" placeholder="Manufacturer">
                                @error('make')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Model</label>
                                <input type="text" name="model"
                                       class="form-control @error('model') is-invalid @enderror"
                                       value="{{ old('model') }}" placeholder="Model number">
                                @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 single-only">
                                <label class="form-label">Serial / Batch No.</label>
                                <input type="text" name="serial_or_batch"
                                       class="form-control @error('serial_or_batch') is-invalid @enderror"
                                       value="{{ old('serial_or_batch') }}" placeholder="Serial or batch">
                                @error('serial_or_batch')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Rating</label>
                                <input type="text" name="rating"
                                       class="form-control @error('rating') is-invalid @enderror"
                                       value="{{ old('rating') }}" placeholder="e.g. 12kN">
                                @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fixing Type</label>
                                <input type="text" name="fixing_type"
                                       class="form-control @error('fixing_type') is-invalid @enderror"
                                       value="{{ old('fixing_type') }}" placeholder="e.g. Through-bolt">
                                @error('fixing_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right column --}}
            <div class="col-lg-4">

                {{-- Dates --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-calendar-line me-2 text-primary"></i>Dates</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Install Date</label>
                            <input type="date" name="install_date"
                                   class="form-control @error('install_date') is-invalid @enderror"
                                   value="{{ old('install_date') }}">
                            @error('install_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">Next Inspection Due</label>
                            <input type="date" name="next_inspection_due_date"
                                   class="form-control @error('next_inspection_due_date') is-invalid @enderror"
                                   value="{{ old('next_inspection_due_date') }}">
                            @error('next_inspection_due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Replacement (single mode only) --}}
                <div class="card mb-3 single-only">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-arrow-left-right-line me-2 text-primary"></i>Replacement</h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label">Replaces Asset</label>
                        <select name="replaces_asset_id"
                                class="form-select form-select-sm @error('replaces_asset_id') is-invalid @enderror">
                            <option value="">— None —</option>
                            @foreach($assets as $a)
                            <option value="{{ $a->id }}" {{ old('replaces_asset_id') == $a->id ? 'selected' : '' }}>
                                {{ $a->asset_code }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-text">Select only if this asset is replacing an existing one.</div>
                        @error('replaces_asset_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2">
                    <button type="submit" id="submitBtn" class="btn btn-primary flex-grow-1">
                        <i class="ri-save-line me-1"></i><span id="submitLabel">Create Asset</span>
                    </button>
                    <a href="{{ route('admin.assets.index') }}" class="btn btn-light">Cancel</a>
                </div>

            </div>
        </div>
    </form>

    @push('scripts')
    <script>
    @php
        $buildingsBySiteJson = \App\Models\Building::all(['id', 'site_id', 'name_or_level', 'building_code'])
            ->groupBy('site_id')
            ->map(fn ($b) => $b->values());
    @endphp
    const buildingsBySite = {!! json_encode($buildingsBySiteJson) !!};
    const oldBuildingId = '{{ old('building_id') }}';

    let currentClientCode    = '';
    let currentBuildingCode  = '';
    let currentAssetTypeCode = '';

    function loadBuildings(siteId, selectedId) {
        const sel = document.getElementById('buildingSelect');
        sel.innerHTML = '<option value="">— Select Building —</option>';
        (buildingsBySite[siteId] || []).forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.id;
            opt.textContent = b.name_or_level;
            opt.dataset.buildingCode = b.building_code || '';
            if (b.id === selectedId) opt.selected = true;
            sel.appendChild(opt);
        });
        currentBuildingCode = '';
        updatePrefixBadges();
    }

    function buildAutoPrefix() {
        // Location parts separated by dash, asset type code appended directly (no trailing dash)
        const locParts = [currentClientCode, currentBuildingCode].filter(Boolean);
        const locStr   = locParts.length ? locParts.join('-') + '-' : '';
        return locStr + (currentAssetTypeCode || '');
    }

    function updatePrefixBadges() {
        const display = buildAutoPrefix();

        // Single mode badge
        const badge = document.getElementById('singleClientCodeBadge');
        if (badge) {
            if (display) { badge.textContent = display; badge.style.display = ''; }
            else { badge.style.display = 'none'; }
        }

        // Range mode prefix display
        const rangePfx = document.getElementById('rangePrefixDisplay');
        if (rangePfx) {
            rangePfx.textContent = display || '—';
        }
    }

    function onAssetTypeChange() {
        const sel = document.getElementById('assetTypeSelect');
        const opt = sel.options[sel.selectedIndex];
        currentAssetTypeCode = opt.dataset.code || '';
        updatePrefixBadges();
        validateRange();
    }

    document.getElementById('siteSelect').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        currentClientCode = opt.dataset.clientCode || '';
        loadBuildings(this.value, null);
        validateRange();
    });

    document.getElementById('buildingSelect').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        currentBuildingCode = opt.dataset.buildingCode || '';
        updatePrefixBadges();
        validateRange();
    });

    const initSite = document.getElementById('siteSelect').value;
    if (initSite) {
        const initOpt = document.getElementById('siteSelect').options[document.getElementById('siteSelect').selectedIndex];
        currentClientCode = initOpt.dataset.clientCode || '';
        loadBuildings(initSite, oldBuildingId);
        if (oldBuildingId) {
            const bSel = document.getElementById('buildingSelect');
            const bOpt = bSel.options[bSel.selectedIndex];
            if (bOpt) currentBuildingCode = bOpt.dataset.buildingCode || '';
        }
    }
    // Restore asset type code on load
    const initAtSel = document.getElementById('assetTypeSelect');
    const initAtOpt = initAtSel.options[initAtSel.selectedIndex];
    if (initAtOpt) currentAssetTypeCode = initAtOpt.dataset.code || '';
    updatePrefixBadges();

    // ── Mode toggle ──────────────────────────────────────────────────────────

    function setMode(mode) {
        document.getElementById('modeInput').value = mode;

        document.querySelectorAll('.single-only').forEach(el => {
            el.style.display = mode === 'single' ? '' : 'none';
        });
        document.querySelectorAll('.range-only').forEach(el => {
            el.style.display = mode === 'range' ? '' : 'none';
        });

        if (mode === 'range') {
            // Clear single-mode fields
            const ac = document.querySelector('[name="asset_code"]');
            if (ac) ac.value = '';
            recalcEnd();
        } else {
            // Clear range-mode fields and indicator
            ['rangeStart', 'rangeEnd', 'quantity'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            document.getElementById('rangeIndicator').innerHTML = '';
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('submitLabel').textContent = 'Create Asset';
        }
    }

    // ── Range: auto-calculate End from Start + Quantity ──────────────────────

    function recalcEnd() {
        const startRaw = document.getElementById('rangeStart').value.trim();
        const qty      = parseInt(document.getElementById('quantity').value, 10);
        const start    = parseInt(startRaw, 10);
        const endInput = document.getElementById('rangeEnd');
        const indicator = document.getElementById('rangeIndicator');
        const btn       = document.getElementById('submitBtn');

        if (isNaN(start) || isNaN(qty) || qty < 1) {
            endInput.value = '';
            indicator.innerHTML = '';
            btn.disabled = true;
            return;
        }

        const end    = start + qty - 1;
        const padLen = Math.max(startRaw.length, String(end).length);
        const pad    = n => String(n).padStart(padLen, '0');
        endInput.value = pad(end);

        const fullPrefix = buildAutoPrefix();
        const first = fullPrefix + pad(start);
        const last  = fullPrefix + pad(end);
        indicator.innerHTML = `<span class="text-success"><i class="ri-check-line me-1"></i>${first} to ${last}</span>`;
        document.getElementById('submitLabel').textContent = `Create ${qty} Assets`;
        btn.disabled = false;
    }

    function updateIndicator() { recalcEnd(); }
    function validateRange()   { recalcEnd(); }

    // ── Init on page load (handles validation-error re-render) ───────────────
    setMode(document.getElementById('modeInput').value);
    </script>
    @endpush

</x-app-layout>
