<x-app-layout>
    <x-slot name="title">Record Installation / Rectification</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Record Installation / Rectification</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('technician.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('technician.jobs.index') }}">My Jobs</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('technician.jobs.show', $job) }}">Job</a></li>
                        <li class="breadcrumb-item active">Install</li>
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
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-border-left alert-dismissible fade show">
        <i class="ri-error-warning-line me-2"></i>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('technician.jobs.install.store', $job) }}" id="installForm">
        @csrf

        <div id="entriesContainer">
            {{-- Entry rows injected by JS, or one default row --}}
            <div class="entry-row card mb-3" data-index="0">
                <div class="card-header d-flex align-items-center">
                    <h6 class="card-title mb-0 flex-grow-1">
                        <i class="ri-tools-line me-2 text-primary"></i>Entry <span class="entry-num">1</span>
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-entry" style="display:none">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
                <div class="card-body" id="entryBody0"></div>
            </div>
        </div>

        <div class="mb-3">
            <button type="button" id="addEntry" class="btn btn-outline-secondary btn-sm">
                <i class="ri-add-line me-1"></i>Add Another Entry
            </button>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="ri-save-line me-1"></i>Save Records
            </button>
            <a href="{{ route('technician.jobs.show', $job) }}" class="btn btn-light">Cancel</a>
        </div>
    </form>

    {{-- Hidden template for JS cloning --}}
    <template id="entryTemplate">
        <div class="entry-row card mb-3" data-index="__IDX__">
            <div class="card-header d-flex align-items-center">
                <h6 class="card-title mb-0 flex-grow-1">
                    <i class="ri-tools-line me-2 text-primary"></i>Entry <span class="entry-num">__NUM__</span>
                </h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-entry">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            <div class="card-body" id="entryBody__IDX__">
                {{-- filled by JS --}}
            </div>
        </div>
    </template>

    @php
    $jsAssets     = $existingAssets->map(fn ($a) => ['id' => $a->id, 'text' => $a->asset_code . ' — ' . ($assetTypes[$a->asset_type] ?? $a->asset_type) . ($a->zone ? ' (' . $a->zone . ')' : '')]);
    $jsBuildings  = $buildings->map(fn ($b) => ['id' => $b->id, 'text' => $b->name_or_level]);
    $jsActions    = \App\Models\InstallationAsset::ACTIONS;
    $jsAssetTypes = $assetTypes;
    @endphp

    {{-- Page data embedded as JSON so VS Code doesn't try to parse Blade @directives as JS --}}
    <script type="application/json" id="installPageData">
    {
        "existingAssets": @json($jsAssets),
        "buildings":      @json($jsBuildings),
        "actions":        @json($jsActions),
        "assetTypes":     @json($jsAssetTypes)
    }
    </script>

    <script>
    const _pageData      = JSON.parse(document.getElementById('installPageData').textContent);
    const existingAssets = _pageData.existingAssets;
    const buildings      = _pageData.buildings;
    const actions        = _pageData.actions;
    const assetTypes     = _pageData.assetTypes;

    function buildEntryHTML(index) {
        const num = index + 1;

        const assetOptions = existingAssets.map(a =>
            `<option value="${a.id}">${a.text}</option>`).join('');

        const actionOptions = actions.map(a =>
            `<option value="${a}">${a.charAt(0).toUpperCase() + a.slice(1)}</option>`).join('');

        const buildingOptions = buildings.map(b =>
            `<option value="${b.id}">${b.text}</option>`).join('');

        const typeOptions = assetTypes.map(t =>
            `<option value="${t}">${t.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase())}</option>`).join('');

        return `
        <div class="mb-3">
            <label class="form-label fw-medium fs-13">Mode</label>
            <div class="d-flex gap-3">
                <div class="form-check">
                    <input class="form-check-input mode-toggle" type="radio"
                           name="entries[${index}][mode]" value="existing"
                           id="mode_existing_${index}" checked>
                    <label class="form-check-label" for="mode_existing_${index}">Work on existing asset</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input mode-toggle" type="radio"
                           name="entries[${index}][mode]" value="new"
                           id="mode_new_${index}">
                    <label class="form-check-label" for="mode_new_${index}">Register new asset</label>
                </div>
            </div>
        </div>

        <div class="existing-fields">
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label fs-12 text-muted mb-1">Asset <span class="text-danger">*</span></label>
                    <select name="entries[${index}][asset_id]" class="form-select form-select-sm">
                        <option value="">— select asset —</option>
                        ${assetOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 text-muted mb-1">Action <span class="text-danger">*</span></label>
                    <select name="entries[${index}][action]" class="form-select form-select-sm">
                        ${actionOptions}
                    </select>
                </div>
                <div class="col-md-9">
                    <label class="form-label fs-12 text-muted mb-1">Material / Work Notes</label>
                    <input type="text" name="entries[${index}][material_notes]"
                           class="form-control form-control-sm" placeholder="Optional notes…">
                </div>
            </div>
        </div>

        <div class="new-fields" style="display:none">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label fs-12 text-muted mb-1">Asset Code <span class="text-danger">*</span></label>
                    <input type="text" name="entries[${index}][asset_code]"
                           class="form-control form-control-sm" placeholder="e.g. AP-001">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 text-muted mb-1">Asset Type <span class="text-danger">*</span></label>
                    <select name="entries[${index}][asset_type]" class="form-select form-select-sm">
                        <option value="">— select —</option>
                        ${typeOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 text-muted mb-1">Action <span class="text-danger">*</span></label>
                    <select name="entries[${index}][action]" class="form-select form-select-sm">
                        ${actionOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 text-muted mb-1">Building</label>
                    <select name="entries[${index}][building_id]" class="form-select form-select-sm">
                        <option value="">— none —</option>
                        ${buildingOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 text-muted mb-1">Zone</label>
                    <input type="text" name="entries[${index}][zone]"
                           class="form-control form-control-sm" placeholder="Zone / level">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 text-muted mb-1">Make</label>
                    <input type="text" name="entries[${index}][make]"
                           class="form-control form-control-sm" placeholder="Manufacturer">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 text-muted mb-1">Model</label>
                    <input type="text" name="entries[${index}][model]"
                           class="form-control form-control-sm" placeholder="Model">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 text-muted mb-1">Serial / Batch</label>
                    <input type="text" name="entries[${index}][serial_or_batch]"
                           class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 text-muted mb-1">Rating</label>
                    <input type="text" name="entries[${index}][rating]"
                           class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 text-muted mb-1">Install Date</label>
                    <input type="date" name="entries[${index}][install_date]"
                           class="form-control form-control-sm">
                </div>
                <div class="col-md-6">
                    <label class="form-label fs-12 text-muted mb-1">Material / Work Notes</label>
                    <input type="text" name="entries[${index}][material_notes]"
                           class="form-control form-control-sm" placeholder="Optional notes…">
                </div>
            </div>
        </div>`;
    }

    // Populate first row
    document.getElementById('entryBody0')
        ? (document.getElementById('entryBody0').innerHTML = buildEntryHTML(0))
        : document.querySelector('.entry-row[data-index="0"] .card-body').innerHTML = buildEntryHTML(0);

    bindEntryEvents(document.querySelector('.entry-row[data-index="0"]'));

    let entryCount = 1;

    document.getElementById('addEntry').addEventListener('click', function () {
        const container = document.getElementById('entriesContainer');
        const idx = entryCount++;

        const div = document.createElement('div');
        div.innerHTML = `
            <div class="entry-row card mb-3" data-index="${idx}">
                <div class="card-header d-flex align-items-center">
                    <h6 class="card-title mb-0 flex-grow-1">
                        <i class="ri-tools-line me-2 text-primary"></i>Entry <span class="entry-num">${idx + 1}</span>
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-entry">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
                <div class="card-body">${buildEntryHTML(idx)}</div>
            </div>`;

        const row = div.firstElementChild;
        container.appendChild(row);
        bindEntryEvents(row);
        updateRemoveButtons();
    });

    function bindEntryEvents(row) {
        // Mode toggle
        row.querySelectorAll('.mode-toggle').forEach(radio => {
            radio.addEventListener('change', function () {
                const ef = row.querySelector('.existing-fields');
                const nf = row.querySelector('.new-fields');
                if (this.value === 'new') {
                    ef.style.display = 'none';
                    nf.style.display = '';
                } else {
                    ef.style.display = '';
                    nf.style.display = 'none';
                }
            });
        });

        // Remove button
        row.querySelector('.remove-entry')?.addEventListener('click', function () {
            row.remove();
            updateRemoveButtons();
        });
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.entry-row');
        rows.forEach(r => {
            const btn = r.querySelector('.remove-entry');
            if (btn) btn.style.display = rows.length > 1 ? '' : 'none';
        });
    }
    </script>
</x-app-layout>
