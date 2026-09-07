<x-app-layout>
    <x-slot name="title">Add Questionnaires</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Add Questionnaires</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.questionnaires.index') }}">Questionnaires</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible alert-border-left fade show" role="alert">
        <i class="ri-error-warning-line me-3 align-middle fs-16"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-questionnaire-line me-2 text-primary"></i>New Questionnaires
                    </h5>
                </div>

                <form method="POST" action="{{ route('admin.questionnaires.store') }}" id="createMultiForm">
                    @csrf
                    <div class="card-body">

                        <div class="row g-3 mb-3" style="max-width:700px;">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Asset Type <span class="text-danger">*</span></label>
                                <select id="createAssetType" class="form-select" required onchange="regenerateAllAutoKeys(); filterSections()">
                                    <option value="">— Select Asset Type —</option>
                                    @foreach($assetTypes as $val => $label)
                                    <option value="{{ $val }}" {{ old('asset_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Questions assigned to an asset type appear in the inspection form for that asset.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Section <span class="text-danger">*</span></label>
                                <select id="createSectionId" class="form-select" required>
                                    <option value="">— Select Section —</option>
                                    @foreach($sections as $sec)
                                    <option value="{{ $sec->id }}"
                                            data-asset-type="{{ $sec->asset_type }}"
                                            {{ old('section_id.0') === $sec->id ? 'selected' : '' }}>
                                        {{ $sec->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <div class="form-text">All questions added below will belong to this section.</div>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div id="qRowsContainer"></div>

                        <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-2" onclick="addQRow()">
                            <i class="ri-add-line me-1"></i> Add Another Question
                        </button>

                    </div>
                    <div class="card-footer d-flex gap-2 justify-content-end">
                        <a href="{{ route('admin.questionnaires.index') }}" class="btn btn-light">
                            <i class="ri-arrow-left-line me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Save All
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Row template --}}
    <template id="qRowTemplate">
        <div class="q-row border rounded p-3 mb-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width:0">
                    <span class="q-num-badge badge fs-11 px-2 py-1 flex-shrink-0"
                          style="min-width:32px;text-align:center;background:#6366f1;color:#fff">Q1</span>
                    <span class="q-name-preview text-muted fs-12 fst-italic text-truncate">New question</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row ms-2"
                        onclick="removeQRow(this)" style="display:none;">
                    <i class="ri-delete-bin-line me-1"></i>Remove
                </button>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-md-5">
                    <label class="form-label form-label-sm">Data Type <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm q-type-select" onchange="onQTypeChange(this)"></select>
                </div>
                <div class="col-md-7 q-ft-wrap" style="display:none;">
                    <label class="form-label form-label-sm q-ft-label">Option Set <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm q-ft-select" onchange="onQFtChange(this)" disabled>
                        <option value="">— Select option set —</option>
                    </select>
                    <div class="q-no-configs form-text text-warning" style="display:none;">
                        <i class="ri-alert-line me-1"></i>No option sets of this type yet.
                        <a href="{{ route('admin.master.data-types.index') }}" target="_blank">Create in Data Types</a>.
                    </div>
                </div>
            </div>

            {{-- Standard section: name / key / options / toggles --}}
            <div class="q-standard-section">
                <div class="row g-2 mb-2">
                    <div class="col-12">
                        <label class="form-label form-label-sm">Question Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm q-name"
                               maxlength="255" placeholder="e.g. Is the roof in good condition?"
                               oninput="qUpdatePreview(this)">
                    </div>
                    <input type="hidden" class="q-key" maxlength="100">
                </div>
                <div class="q-options-preview mb-2 p-2 rounded border bg-light" style="display:none;">
                    <small class="text-muted me-1">Options:</small>
                    <span class="q-options-badges"></span>
                </div>
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input q-enabled-cb" type="checkbox" checked>
                            <label class="form-check-label form-label-sm mb-0">
                                Enabled <span class="text-muted">(visible to Technicians)</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input q-required-cb" type="checkbox" checked>
                            <label class="form-check-label form-label-sm mb-0">
                                Required <span class="text-muted">(answer is mandatory)</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2 ms-auto">
                        <select class="form-select form-select-sm q-status">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Sub-questionnaire section: manual parent entry + nested sq-rows --}}
            <div class="q-sub-section mt-2" style="display:none;">

                {{-- Parent question — always Switch type --}}
                <div class="border rounded p-3 mb-3 bg-light">
                    <p class="fw-semibold fs-12 text-muted text-uppercase mb-2">
                        <i class="ri-parent-line me-1"></i>Parent Question
                        <span class="badge bg-primary-subtle text-primary fw-normal ms-1">Switch</span>
                    </p>
                    <div class="row g-2 mb-2">
                        <div class="col-md-5">
                            <label class="form-label form-label-sm">Switch Option Set <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm sq-parent-ft-select" onchange="onParentFtChange(this)" disabled>
                                <option value="">— Select option set —</option>
                            </select>
                            <div class="sq-parent-no-configs form-text text-warning" style="display:none;">
                                <i class="ri-alert-line me-1"></i>No switch option sets yet.
                                <a href="{{ route('admin.master.data-types.index') }}" target="_blank">Create in Data Types</a>.
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <label class="form-label form-label-sm">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm sq-parent-name"
                                   maxlength="255" placeholder="e.g. Is the equipment operational?"
                                   oninput="qUpdatePreview(this)">
                        </div>
                        <input type="hidden" class="sq-parent-key" maxlength="100">
                    </div>
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input sq-parent-enabled-cb" type="checkbox" checked>
                                <label class="form-check-label form-label-sm mb-0">Enabled</label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input sq-parent-required-cb" type="checkbox" checked>
                                <label class="form-check-label form-label-sm mb-0">Required</label>
                            </div>
                        </div>
                        <div class="col-md-2 ms-auto">
                            <select class="form-select form-select-sm sq-parent-status">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Sub-questions --}}
                <p class="fw-semibold fs-12 text-muted text-uppercase mb-2">
                    <i class="ri-list-check-2 me-1"></i>Sub-Questions
                </p>
                <div class="sq-container ps-3 border-start border-2 border-primary-subtle"></div>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-2"
                        onclick="addSubQRow(this.closest('.q-row'))">
                    <i class="ri-add-line me-1"></i> Add Sub-question
                </button>
            </div>

        </div>
    </template>

    @push('scripts')
    <script>
    const ALL_FIELD_TYPES  = {!! json_encode($fieldTypesForJs) !!};
    const TYPE_OPTIONS_MAP = {!! json_encode($typeOptions) !!};
    const TYPES_WITH_OPTS  = ['switch', 'three_tier_switch', 'option_list'];
    const SUB_Q_TYPE       = 'sub_questionnaire';
    const DATA_TYPES_URL   = "{{ route('admin.master.data-types.index') }}";

    function filterSections() {
        const assetType = document.getElementById('createAssetType').value;
        const sectionSel = document.getElementById('createSectionId');
        const current = sectionSel.value;
        Array.from(sectionSel.options).forEach(opt => {
            if (!opt.value) return; // keep the "No section" option
            const matches = !assetType || opt.dataset.assetType === assetType;
            opt.hidden = !matches;
            opt.disabled = !matches;
        });
        // Reset selection if currently selected option is now hidden
        const selectedOpt = sectionSel.options[sectionSel.selectedIndex];
        if (selectedOpt && selectedOpt.hidden) sectionSel.value = '';
    }
    // Run on page load to apply any pre-selected asset type (e.g. after validation failure)
    document.addEventListener('DOMContentLoaded', filterSections);

    function buildTypeOptionsHtml() {
        let html = '<option value="">— Select —</option>';
        for (const [val, lbl] of Object.entries(TYPE_OPTIONS_MAP))
            html += `<option value="${val}">${lbl}</option>`;
        return html;
    }

    function buildSubTypeOptionsHtml() {
        let html = '<option value="">— Select —</option>';
        for (const [val, lbl] of Object.entries(TYPE_OPTIONS_MAP))
            if (val !== SUB_Q_TYPE) html += `<option value="${val}">${lbl}</option>`;
        return html;
    }

    function sqEsc(str) {
        return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function populateFtSelect(sel, warn, type, selectedId) {
        sel.innerHTML = '<option value="">— Select option set —</option>';
        const types = Array.isArray(type) ? type : [type];
        const matching = ALL_FIELD_TYPES.filter(ft => types.includes(ft.type));
        if (matching.length === 0) { sel.disabled = true; warn.style.display = ''; return; }
        warn.style.display = 'none'; sel.disabled = false;
        matching.forEach(ft => {
            const o = document.createElement('option');
            o.value = ft.id; o.textContent = ft.name;
            o.dataset.options = JSON.stringify(ft.options);
            o.dataset.ftType  = ft.type;
            if (ft.id === selectedId) o.selected = true;
            sel.appendChild(o);
        });
    }

    function showOptionBadges(sel, prevEl, badgesEl) {
        const chosen = sel.options[sel.selectedIndex];
        if (!chosen || !chosen.value) { prevEl.style.display = 'none'; return; }
        const opts = JSON.parse(chosen.dataset.options || '[]');
        if (opts.length > 0) {
            prevEl.style.display = '';
            badgesEl.innerHTML = opts.map(o =>
                `<span class="badge bg-white border text-dark me-1 px-2 py-1">${o}</span>`).join('');
        } else { prevEl.style.display = 'none'; }
    }

    // ── Auto key generation ──────────────────────────────────────────────────
    const TYPE_ABBR = { switch:'sw', three_tier_switch:'tts', text:'txt', number:'num', option_list:'opt',
                        sub_questionnaire:'sub', date:'dt', textarea:'ta', photo:'photo' };

    function slugify(str) {
        return (str||'').toLowerCase().replace(/[^a-z0-9]+/g,'_').replace(/^_+|_+$/g,'');
    }

    function collectUsedKeys(skipEl) {
        const keys = new Set();
        document.querySelectorAll('#qRowsContainer .q-key, #qRowsContainer .sq-parent-key, #qRowsContainer .sq-key').forEach(el => {
            if (el !== skipEl && el.value) keys.add(el.value);
        });
        return keys;
    }

    function buildKeyBase(dataType) {
        const assetVal  = document.getElementById('createAssetType').value;
        const assetSlug = assetVal ? slugify(assetVal) : 'gen';
        const typeSlug  = TYPE_ABBR[dataType] || slugify(dataType) || 'q';
        return assetSlug + '_' + typeSlug;
    }

    function makeUniqueKey(base, usedKeys) {
        let n = 1, key;
        do { key = base + '_' + n++; } while (usedKeys.has(key));
        return key;
    }

    function autoFillKey(inputEl, dataType) {
        if (!inputEl || !dataType || inputEl.dataset.auto === 'false') return;
        const base = buildKeyBase(dataType);
        inputEl.value = makeUniqueKey(base, collectUsedKeys(inputEl));
        inputEl.dataset.auto = 'true';
    }

    function attachKeyListener(inputEl) {
        if (!inputEl) return;
        inputEl.dataset.auto = 'true';
        inputEl.readOnly = true;
        inputEl.classList.add('bg-light');
    }

    function regenerateAllAutoKeys() {
        const usedKeys = new Set();
        document.querySelectorAll('#qRowsContainer .q-key, #qRowsContainer .sq-parent-key, #qRowsContainer .sq-key').forEach(el => {
            if (el.dataset.auto === 'false') { if (el.value) usedKeys.add(el.value); return; }
            let dataType = '';
            if (el.classList.contains('q-key')) {
                dataType = el.closest('.q-row')?.querySelector('.q-type-select')?.value || '';
            } else if (el.classList.contains('sq-parent-key')) {
                dataType = 'switch';
            } else if (el.classList.contains('sq-key')) {
                dataType = el.closest('.sq-row')?.querySelector('.sq-type-select')?.value || '';
            }
            if (!dataType) return;
            const base = buildKeyBase(dataType);
            const key  = makeUniqueKey(base, usedKeys);
            el.value   = key;
            usedKeys.add(key);
        });
    }

    // ── Outer row management ─────────────────────────────────────────────────
    function addQRow(prefill) {
        prefill = prefill || {};
        const tmpl  = document.getElementById('qRowTemplate');
        const clone = tmpl.content.cloneNode(true);
        clone.querySelector('.q-type-select').innerHTML = buildTypeOptionsHtml();
        document.getElementById('qRowsContainer').appendChild(clone);
        updateRowNumbers();

        const liveRow = [...document.querySelectorAll('#qRowsContainer .q-row')].at(-1);
        attachKeyListener(liveRow.querySelector('.q-key'));
        attachKeyListener(liveRow.querySelector('.sq-parent-key'));
        if (prefill.name)   liveRow.querySelector('.q-name').value   = prefill.name;
        if (prefill.key)    liveRow.querySelector('.q-key').value    = prefill.key;
        if (prefill.status) liveRow.querySelector('.q-status').value = prefill.status;
        liveRow.querySelector('.q-enabled-cb').checked  = prefill.enabled  !== '0';
        liveRow.querySelector('.q-required-cb').checked = prefill.required !== '0';

        if (prefill.type) {
            const typeSel = liveRow.querySelector('.q-type-select');
            typeSel.value = prefill.type;
            onQTypeChange(typeSel);
            if (prefill.field_type_id) {
                liveRow.querySelector('.q-ft-select').value = prefill.field_type_id;
                onQFtChange(liveRow.querySelector('.q-ft-select'));
            }
        }
    }

    function removeQRow(btn) { btn.closest('.q-row').remove(); updateRowNumbers(); }

    const Q_COLORS = ['#6366f1','#f97316','#10b981','#3b82f6','#ec4899','#eab308','#8b5cf6','#14b8a6'];
    const SQ_COLORS = ['#10b981','#6366f1','#f97316','#3b82f6','#ec4899','#eab308','#8b5cf6','#14b8a6'];

    function updateRowNumbers() {
        const rows = [...document.querySelectorAll('#qRowsContainer .q-row')];
        rows.forEach((row, i) => {
            const color = Q_COLORS[i % Q_COLORS.length];
            row.style.borderLeft = `4px solid ${color}`;
            const badge = row.querySelector('.q-num-badge');
            if (badge) { badge.textContent = `Q${i + 1}`; badge.style.background = color; }
            row.querySelector('.btn-remove-row').style.display = rows.length > 1 ? '' : 'none';
        });
    }

    function qUpdatePreview(nameInput) {
        const preview = nameInput.closest('.q-row')?.querySelector('.q-name-preview');
        if (preview) preview.textContent = nameInput.value.trim() || 'New question';
    }

    function renumberSqRows(qRow) {
        const rows = [...qRow.querySelectorAll('.sq-container .sq-row')];
        rows.forEach((row, i) => {
            const color = SQ_COLORS[i % SQ_COLORS.length];
            row.style.borderLeft = `4px solid ${color}`;
            const badge = row.querySelector('.sq-num-badge');
            if (badge) { badge.textContent = `S${i + 1}`; badge.style.background = color; }
            row.querySelector('.sq-remove-btn').style.display = rows.length > 1 ? '' : 'none';
        });
    }

    function sqUpdatePreview(nameInput) {
        const preview = nameInput.closest('.sq-row')?.querySelector('.sq-name-preview');
        if (preview) preview.textContent = nameInput.value.trim() || 'New sub-question';
    }

    function onQTypeChange(selectEl) {
        const row        = selectEl.closest('.q-row');
        const type       = selectEl.value;
        const stdSection = row.querySelector('.q-standard-section');
        const subSection = row.querySelector('.q-sub-section');
        const ftWrap     = row.querySelector('.q-ft-wrap');
        const ftSel      = row.querySelector('.q-ft-select');
        const warn       = row.querySelector('.q-no-configs');
        const prev       = row.querySelector('.q-options-preview');
        const label      = row.querySelector('.q-ft-label');

        prev.style.display = 'none';
        ftSel.innerHTML = '<option value="">— Select option set —</option>';
        ftSel.disabled = true; ftWrap.style.display = 'none';

        if (type === SUB_Q_TYPE) {
            stdSection.style.display = 'none';
            subSection.style.display = '';
            // Populate parent switch option set the first time
            const parentFtSel  = row.querySelector('.sq-parent-ft-select');
            const parentFtWarn = row.querySelector('.sq-parent-no-configs');
            if (parentFtSel && parentFtSel.options.length <= 1) {
                populateFtSelect(parentFtSel, parentFtWarn, ['switch', 'three_tier_switch'], null);
            }
            const sqCont = row.querySelector('.sq-container');
            if (sqCont && sqCont.querySelectorAll('.sq-row').length === 0) addSubQRow(row);
            autoFillKey(row.querySelector('.sq-parent-key'), 'switch');
            return;
        }

        stdSection.style.display = '';
        subSection.style.display = 'none';
        if (type) autoFillKey(row.querySelector('.q-key'), type);
        if (!TYPES_WITH_OPTS.includes(type)) return;

        ftWrap.style.display = '';
        label.innerHTML = (type === 'switch' ? 'Switch Option Set' : 'Option List Set')
                         + ' <span class="text-danger">*</span>';
        populateFtSelect(ftSel, warn, type, null);
    }

    function onQFtChange(selectEl) {
        const row = selectEl.closest('.q-row');
        showOptionBadges(selectEl, row.querySelector('.q-options-preview'), row.querySelector('.q-options-badges'));
    }

    // ── Parent switch option set changed — refresh sub-row condition dropdowns ─
    function onParentFtChange(selectEl) {
        const qRow   = selectEl.closest('.q-row');
        const chosen = selectEl.options[selectEl.selectedIndex];
        const opts   = (chosen && chosen.value) ? JSON.parse(chosen.dataset.options || '[]') : [];
        const ftType = chosen?.dataset.ftType || '';
        qRow.querySelectorAll('.sq-container .sq-row').forEach(sqRow => {
            refreshConditionOptions(sqRow, opts, ftType);
        });
    }

    function refreshConditionOptions(sqRow, opts, ftType) {
        const condSel = sqRow.querySelector('.sq-condition');
        if (!condSel) return;
        const prev = condSel.value;
        const isThreeTier = ftType === 'three_tier_switch' || opts.length >= 3;
        condSel.innerHTML = '<option value="">— Select —</option>';
        if (isThreeTier) {
            if (opts[0]) condSel.innerHTML += `<option value="opt1">${sqEsc(opts[0])}</option>`;
            if (opts[1]) condSel.innerHTML += `<option value="opt2">${sqEsc(opts[1])}</option>`;
            if (opts[2]) condSel.innerHTML += `<option value="opt3">${sqEsc(opts[2])}</option>`;
        } else {
            if (opts[0]) condSel.innerHTML += `<option value="yes">${sqEsc(opts[0])}</option>`;
            if (opts[1]) condSel.innerHTML += `<option value="no">${sqEsc(opts[1])}</option>`;
        }
        if (prev) condSel.value = prev;
    }

    // ── Sub-question row management ──────────────────────────────────────────
    function addSubQRow(qRow, prefill) {
        prefill = prefill || {};
        const container = qRow.querySelector('.sq-container');
        // Get current parent switch options to populate condition dropdown
        const parentFtSel  = qRow.querySelector('.sq-parent-ft-select');
        const parentChosen = parentFtSel?.options[parentFtSel.selectedIndex];
        const parentOpts   = (parentChosen && parentChosen.value)
                             ? JSON.parse(parentChosen.dataset.options || '[]') : [];
        const parentFtType = parentChosen?.dataset.ftType || '';

        const div = document.createElement('div');
        div.className = 'sq-row border rounded p-3 mb-2';
        div.innerHTML = `
            <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
                <span class="sq-num-badge badge fs-11 px-2 py-1 flex-shrink-0"
                      style="min-width:32px;text-align:center;background:#10b981;color:#fff">S1</span>
                <span class="sq-name-preview text-muted fs-12 fst-italic text-truncate">
                    ${sqEsc(prefill.name || 'New sub-question')}
                </span>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-12">
                    <label class="form-label form-label-sm">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm sq-name"
                           maxlength="255" value="${sqEsc(prefill.name || '')}" placeholder="Sub-question name"
                           oninput="sqUpdatePreview(this)">
                </div>
                <input type="hidden" class="sq-key" maxlength="100" value="${sqEsc(prefill.key || '')}">
            </div>
            <div class="row g-2 mb-2">
                <div class="col-md-5">
                    <label class="form-label form-label-sm">Data Type <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm sq-type-select" onchange="onSqTypeChange(this)">
                        ${buildSubTypeOptionsHtml()}
                    </select>
                </div>
                <div class="col-md-7 sq-ft-wrap" style="display:none;">
                    <label class="form-label form-label-sm sq-ft-label">Option Set <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm sq-ft-select" onchange="onSqFtChange(this)" disabled>
                        <option value="">— Select option set —</option>
                    </select>
                    <div class="sq-no-configs form-text text-warning" style="display:none;">
                        <i class="ri-alert-line me-1"></i>No option sets of this type yet.
                        <a href="${DATA_TYPES_URL}" target="_blank">Create in Data Types</a>.
                    </div>
                </div>
            </div>
            <div class="sq-options-preview mb-2 p-2 rounded border bg-light" style="display:none;">
                <small class="text-muted me-1">Options:</small><span class="sq-options-badges"></span>
            </div>
            <div class="sq-condition-wrap mb-2">
                <label class="form-label form-label-sm">Show when parent answer is <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm sq-condition">
                    <option value="">— Select —</option>
                    ${(() => {
                        const isThreeTier = parentFtType === 'three_tier_switch' || parentOpts.length >= 3;
                        if (isThreeTier) {
                            return (parentOpts[0] ? `<option value="opt1" ${prefill.condition==='opt1'?'selected':''}>${sqEsc(parentOpts[0])}</option>` : '')
                                 + (parentOpts[1] ? `<option value="opt2" ${prefill.condition==='opt2'?'selected':''}>${sqEsc(parentOpts[1])}</option>` : '')
                                 + (parentOpts[2] ? `<option value="opt3" ${prefill.condition==='opt3'?'selected':''}>${sqEsc(parentOpts[2])}</option>` : '');
                        } else {
                            return (parentOpts[0] ? `<option value="yes" ${prefill.condition==='yes'?'selected':''}>${sqEsc(parentOpts[0])}</option>` : '')
                                 + (parentOpts[1] ? `<option value="no"  ${prefill.condition==='no' ?'selected':''}>${sqEsc(parentOpts[1])}</option>` : '');
                        }
                    })()}
                </select>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input sq-enabled-cb" type="checkbox" ${prefill.enabled !== '0' ? 'checked' : ''}>
                    <label class="form-check-label form-label-sm mb-0">Enabled</label>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input sq-required-cb" type="checkbox" checked>
                    <label class="form-check-label form-label-sm mb-0">Required</label>
                </div>
                <select class="form-select form-select-sm sq-status" style="width:auto;min-width:100px;">
                    <option value="active"   ${(prefill.status || 'active') === 'active'   ? 'selected' : ''}>Active</option>
                    <option value="inactive" ${prefill.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                </select>
                <button type="button" class="btn btn-sm btn-outline-danger ms-auto sq-remove-btn"
                        onclick="removeSqRow(this)" style="display:none;">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>`;

        container.appendChild(div);
        attachKeyListener(div.querySelector('.sq-key'));
        renumberSqRows(qRow);

        if (prefill.type) {
            const typeSel = div.querySelector('.sq-type-select');
            typeSel.value = prefill.type;
            onSqTypeChange(typeSel);
            if (prefill.field_type_id) {
                div.querySelector('.sq-ft-select').value = prefill.field_type_id;
                onSqFtChange(div.querySelector('.sq-ft-select'));
            }
        }
    }

    function removeSqRow(btn) {
        const sqRow = btn.closest('.sq-row');
        const qRow  = sqRow.closest('.q-row');
        sqRow.remove();
        renumberSqRows(qRow);
    }

    function onSqTypeChange(selectEl) {
        const row    = selectEl.closest('.sq-row');
        const type   = selectEl.value;
        const ftWrap = row.querySelector('.sq-ft-wrap');
        const ftSel  = row.querySelector('.sq-ft-select');
        const warn   = row.querySelector('.sq-no-configs');
        const prev   = row.querySelector('.sq-options-preview');
        const label  = row.querySelector('.sq-ft-label');
        prev.style.display = 'none';
        ftSel.innerHTML = '<option value="">— Select option set —</option>';
        ftSel.disabled = true; ftWrap.style.display = 'none';
        if (type) autoFillKey(row.querySelector('.sq-key'), type);
        if (!TYPES_WITH_OPTS.includes(type)) return;
        ftWrap.style.display = '';
        label.innerHTML = (type === 'switch' ? 'Switch Option Set' : 'Option List Set')
                         + ' <span class="text-danger">*</span>';
        populateFtSelect(ftSel, warn, type, null);
    }

    function onSqFtChange(selectEl) {
        const row = selectEl.closest('.sq-row');
        showOptionBadges(selectEl, row.querySelector('.sq-options-preview'), row.querySelector('.sq-options-badges'));
    }

    // ── Pre-submit: flatten into hidden inputs ───────────────────────────────
    document.getElementById('createMultiForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        form.querySelectorAll('input[type="hidden"][name$="[]"]').forEach(el => el.remove());
        form.querySelectorAll('input[type="hidden"][name="asset_type"]').forEach(el => el.remove());

        const payload    = [];
        const sectionId  = document.getElementById('createSectionId').value;
        const assetType  = document.getElementById('createAssetType').value;

        // Add asset_type as a single top-level field
        const atInput = document.createElement('input');
        atInput.type = 'hidden'; atInput.name = 'asset_type'; atInput.value = assetType;
        form.appendChild(atInput);

        document.querySelectorAll('#qRowsContainer .q-row').forEach((row, rowIdx) => {
            const type = row.querySelector('.q-type-select').value;

            if (type === SUB_Q_TYPE) {
                const groupSeq     = 'g' + rowIdx;
                const parentFtSel  = row.querySelector('.sq-parent-ft-select');
                const parentFtOpt  = parentFtSel?.options[parentFtSel?.selectedIndex];
                const parentActualType = parentFtOpt?.dataset.ftType || 'switch';

                // Parent question — type comes from the selected option set
                payload.push({
                    name:            row.querySelector('.sq-parent-name').value,
                    key:             row.querySelector('.sq-parent-key').value,
                    type:            parentActualType,
                    field_type_id:   parentFtSel?.value || '',
                    section_id:      sectionId,
                    parent_id:       '',
                    condition:       '',
                    enabled:         row.querySelector('.sq-parent-enabled-cb').checked ? '1' : '0',
                    required:        row.querySelector('.sq-parent-required-cb').checked ? '1' : '0',
                    status:          row.querySelector('.sq-parent-status').value,
                    is_group_parent: '1',
                    group_seq:       groupSeq,
                });

                // Sub-question children
                row.querySelectorAll('.sq-container .sq-row').forEach(sqRow => {
                    const sqType   = sqRow.querySelector('.sq-type-select').value;
                    const sqFtWrap = sqRow.querySelector('.sq-ft-wrap');
                    payload.push({
                        name:            sqRow.querySelector('.sq-name').value,
                        key:             sqRow.querySelector('.sq-key').value,
                        type:            sqType,
                        field_type_id:   (sqFtWrap && sqFtWrap.style.display !== 'none')
                                         ? (sqRow.querySelector('.sq-ft-select')?.value || '') : '',
                        section_id:      sectionId,
                        parent_id:       '',
                        condition:       sqRow.querySelector('.sq-condition')?.value || '',
                        enabled:         sqRow.querySelector('.sq-enabled-cb').checked ? '1' : '0',
                        required:        sqRow.querySelector('.sq-required-cb').checked ? '1' : '0',
                        status:          sqRow.querySelector('.sq-status').value,
                        is_group_parent: '0',
                        group_seq:       groupSeq,
                    });
                });

            } else {
                const ftWrap = row.querySelector('.q-ft-wrap');
                payload.push({
                    name:            row.querySelector('.q-name').value,
                    key:             row.querySelector('.q-key').value,
                    type:            type,
                    field_type_id:   (ftWrap && ftWrap.style.display !== 'none')
                                     ? (row.querySelector('.q-ft-select')?.value || '') : '',
                    section_id:      sectionId,
                    parent_id:       '',
                    condition:       '',
                    enabled:         row.querySelector('.q-enabled-cb').checked ? '1' : '0',
                    required:        row.querySelector('.q-required-cb').checked ? '1' : '0',
                    status:          row.querySelector('.q-status').value,
                    is_group_parent: '0',
                    group_seq:       '',
                });
            }
        });

        payload.forEach(d => {
            ['name','key','type','field_type_id','section_id','parent_id','condition',
             'enabled','required','status','is_group_parent','group_seq'].forEach(f => {
                const hi = document.createElement('input');
                hi.type = 'hidden'; hi.name = f + '[]'; hi.value = d[f] || '';
                form.appendChild(hi);
            });
        });

        form.submit();
    });

    // ── Init ────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        if (<?= ($errors->any() && count(old('name', [])) > 0) ? 'true' : 'false' ?>) {
        const od = {
            name:          {!! json_encode(old('name', [])) !!},
            key:           {!! json_encode(old('key', [])) !!},
            type:          {!! json_encode(old('type', [])) !!},
            ftId:          {!! json_encode(old('field_type_id', [])) !!},
            condition:     {!! json_encode(old('condition', [])) !!},
            enabled:       {!! json_encode(old('enabled', [])) !!},
            required:      {!! json_encode(old('required', [])) !!},
            status:        {!! json_encode(old('status', [])) !!},
            isGroupParent: {!! json_encode(old('is_group_parent', [])) !!},
            groupSeq:      {!! json_encode(old('group_seq', [])) !!},
        };

        // Group by group_seq
        const groups = {};
        od.name.forEach((n, i) => {
            const seq  = od.groupSeq[i] || '';
            const item = { name: n, key: od.key[i], type: od.type[i],
                           field_type_id: od.ftId[i] || '', condition: od.condition[i] || '',
                           enabled: od.enabled[i], required: od.required[i], status: od.status[i] };
            if (!seq) { groups['__standalone_' + i] = { standalone: item }; return; }
            if (!groups[seq]) groups[seq] = { parent: null, children: [] };
            if ((od.isGroupParent[i] || '0') === '1') groups[seq].parent = item;
            else groups[seq].children.push(item);
        });

        // Restore in order of first occurrence
        const seen = new Set();
        od.groupSeq.forEach((seq, i) => {
            const key = seq || ('__standalone_' + i);
            if (seen.has(key)) return;
            seen.add(key);
            const g = groups[key];
            if (!g) return;

            if (g.standalone) {
                addQRow(g.standalone);
                return;
            }

            // Sub-questionnaire group: add UI row as SUB_Q_TYPE, then restore children
            addQRow({ type: SUB_Q_TYPE });
            const liveRow = [...document.querySelectorAll('#qRowsContainer .q-row')].at(-1);
            const par = g.parent || {};

            if (par.name)   liveRow.querySelector('.sq-parent-name').value   = par.name;
            if (par.key)    liveRow.querySelector('.sq-parent-key').value    = par.key;
            if (par.status) liveRow.querySelector('.sq-parent-status').value = par.status;
            liveRow.querySelector('.sq-parent-enabled-cb').checked  = par.enabled  !== '0';
            liveRow.querySelector('.sq-parent-required-cb').checked = par.required !== '0';

            if (par.field_type_id) {
                const pfSel = liveRow.querySelector('.sq-parent-ft-select');
                if (pfSel) { pfSel.value = par.field_type_id; onParentFtChange(pfSel); }
            }

            // Remove the auto-added blank sub-question, then restore saved ones
            liveRow.querySelector('.sq-container').querySelectorAll('.sq-row').forEach(r => r.remove());
            g.children.forEach(child => addSubQRow(liveRow, child));
            if (liveRow.querySelector('.sq-container').querySelectorAll('.sq-row').length === 0)
                addSubQRow(liveRow);
            renumberSqRows(liveRow);
        });
        } else {
        addQRow();
        }
    });
    </script>
    @endpush
</x-app-layout>
