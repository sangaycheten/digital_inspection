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

                            {{-- Single mode: asset_code + group_id --}}
                            <div class="col-md-4 single-only">
                                <label class="form-label">Asset Code <span class="text-danger">*</span></label>
                                <input type="text" name="asset_code"
                                       class="form-control @error('asset_code') is-invalid @enderror"
                                       value="{{ old('asset_code') }}" placeholder="e.g. AP01">
                                <div class="form-text">Must be unique within the selected site.</div>
                                @error('asset_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 single-only">
                                <label class="form-label">Group ID
                                    <span class="text-muted fs-11"><i class="ri-information-line"
                                        title="Optional: links assets from the same batch"></i></span>
                                </label>
                                <input type="text" name="group_id"
                                       class="form-control @error('group_id') is-invalid @enderror"
                                       value="{{ old('group_id') }}" placeholder="Optional">
                                @error('group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Range mode: prefix / start / end / quantity / indicator --}}
                            <div class="col-md-3 range-only">
                                <label class="form-label">Prefix <span class="text-danger">*</span></label>
                                <input type="text" name="prefix" id="rangePrefix"
                                       class="form-control @error('prefix') is-invalid @enderror"
                                       value="{{ old('prefix') }}" placeholder="e.g. AP"
                                       oninput="updateIndicator()">
                                @error('prefix')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2 range-only">
                                <label class="form-label">Start <span class="text-danger">*</span></label>
                                <input type="text" name="range_start" id="rangeStart" inputmode="numeric"
                                       class="form-control @error('range_start') is-invalid @enderror"
                                       value="{{ old('range_start') }}" placeholder="01"
                                       oninput="recalcRange()">
                                @error('range_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2 range-only">
                                <label class="form-label">End <span class="text-danger">*</span></label>
                                <input type="text" name="range_end" id="rangeEnd" inputmode="numeric"
                                       class="form-control @error('range_end') is-invalid @enderror"
                                       value="{{ old('range_end') }}" placeholder="06"
                                       oninput="recalcRange()">
                                @error('range_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2 range-only">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity"
                                       class="form-control @error('quantity') is-invalid @enderror"
                                       value="{{ old('quantity') }}" placeholder="6" min="1"
                                       oninput="validateRange()">
                                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3 range-only d-flex align-items-end pb-1">
                                <div id="rangeIndicator" class="fs-13 lh-sm"></div>
                            </div>

                            {{-- Asset Type (both modes) --}}
                            <div class="col-md-4">
                                <label class="form-label">Asset Type <span class="text-danger">*</span></label>
                                <select name="asset_type"
                                        class="form-select @error('asset_type') is-invalid @enderror" required>
                                    <option value="">— Select Type —</option>
                                    @foreach($assetTypes as $val => $label)
                                    <option value="{{ $val }}" {{ old('asset_type') == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('asset_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
    const buildingsBySite = @json(
        \App\Models\Building::all(['id', 'site_id', 'name_or_level'])
            ->groupBy('site_id')
            ->map(fn ($b) => $b->values())
    );
    const oldBuildingId = '{{ old('building_id') }}';

    function loadBuildings(siteId, selectedId) {
        const sel = document.getElementById('buildingSelect');
        sel.innerHTML = '<option value="">— Select Building —</option>';
        (buildingsBySite[siteId] || []).forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.id;
            opt.textContent = b.name_or_level;
            if (b.id === selectedId) opt.selected = true;
            sel.appendChild(opt);
        });
    }

    document.getElementById('siteSelect').addEventListener('change', function () {
        loadBuildings(this.value, null);
    });

    const initSite = document.getElementById('siteSelect').value;
    if (initSite) loadBuildings(initSite, oldBuildingId);

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
            validateRange();
        } else {
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('submitLabel').textContent = 'Create Asset';
        }
    }

    // ── Range validation ─────────────────────────────────────────────────────

    function recalcRange() {
        const startRaw = document.getElementById('rangeStart').value.trim();
        const endRaw   = document.getElementById('rangeEnd').value.trim();
        const start    = parseInt(startRaw, 10);
        const end      = parseInt(endRaw, 10);
        const qtyField = document.getElementById('quantity');

        if (!isNaN(start) && !isNaN(end) && end >= start) {
            qtyField.value = end - start + 1;
        } else {
            qtyField.value = '';
        }
        validateRange();
    }

    function updateIndicator() {
        validateRange();
    }

    function validateRange() {
        const mode = document.getElementById('modeInput').value;
        if (mode !== 'range') return;

        const startRaw = document.getElementById('rangeStart').value.trim();
        const endRaw   = document.getElementById('rangeEnd').value.trim();
        const start    = parseInt(startRaw, 10);
        const end      = parseInt(endRaw, 10);
        const qty      = parseInt(document.getElementById('quantity').value, 10);
        const prefix   = document.getElementById('rangePrefix').value.trim();
        const indicator = document.getElementById('rangeIndicator');
        const btn       = document.getElementById('submitBtn');

        if (isNaN(start) || isNaN(end) || isNaN(qty)) {
            indicator.innerHTML = '';
            btn.disabled = true;
            return;
        }

        if (end < start) {
            indicator.innerHTML = '<span class="text-danger"><i class="ri-error-warning-line"></i> End must be ≥ Start</span>';
            btn.disabled = true;
            return;
        }

        const expected = end - start + 1;
        const padLen   = endRaw.length;
        const pad      = n => String(n).padStart(padLen, '0');

        if (qty !== expected) {
            indicator.innerHTML = `<span class="text-danger"><i class="ri-error-warning-line"></i> Quantity must be ${expected}</span>`;
            btn.disabled = true;
            return;
        }

        const first = (prefix || '') + pad(start);
        const last  = (prefix || '') + pad(end);
        indicator.innerHTML = `<span class="text-success"><i class="ri-check-line"></i> ${first}–${last}</span>`;
        document.getElementById('submitLabel').textContent = `Create ${expected} Assets`;
        btn.disabled = false;
    }

    // ── Init on page load (handles validation-error re-render) ───────────────
    setMode(document.getElementById('modeInput').value);
    </script>
    @endpush

</x-app-layout>
