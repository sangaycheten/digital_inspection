<x-app-layout>
    <x-slot name="title">Add Questionnaires</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Add Questionnaires</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.master.questionnaires.index') }}">Questionnaires</a></li>
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

                <form method="POST" action="{{ route('admin.master.questionnaires.store') }}" id="createMultiForm">
                    @csrf
                    <div class="card-body">

                        <div class="mb-3" style="max-width:460px;">
                            <label class="form-label fw-medium">Section</label>
                            <select id="createSectionId" class="form-select">
                                <option value="">— No section —</option>
                                @foreach($sections as $sec)
                                <option value="{{ $sec->id }}" {{ old('section_id.0') === $sec->id ? 'selected' : '' }}>
                                    {{ $sec->name }}
                                </option>
                                @endforeach
                            </select>
                            <div class="form-text">All questions added below will belong to this section.</div>
                        </div>

                        <hr class="my-3">

                        <div id="qRowsContainer"></div>

                        <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-2" onclick="addQRow()">
                            <i class="ri-add-line me-1"></i> Add Another Question
                        </button>

                    </div>
                    <div class="card-footer d-flex gap-2 justify-content-end">
                        <a href="{{ route('admin.master.questionnaires.index') }}" class="btn btn-light">
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
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-primary-subtle text-primary q-row-num fs-12">Question #1</span>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"
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

            <div class="q-standard-section">
                <div class="row g-2 mb-2">
                    <div class="col-md-7">
                        <label class="form-label form-label-sm">Question Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm q-name"
                               maxlength="255" placeholder="e.g. Is the roof in good condition?">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label form-label-sm">Key <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm font-monospace q-key"
                               maxlength="100" placeholder="e.g. roof_condition">
                    </div>
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

            <div class="q-sub-section" style="display:none;">
                <div class="mb-2">
                    <label class="form-label form-label-sm">Parent Questionnaire <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm q-parent-select">
                        <option value="">— Select parent questionnaire —</option>
                        @foreach($parentQuestionnaires as $pq)
                        <option value="{{ $pq->id }}">{{ $pq->name }} ({{ $pq->key }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="border rounded p-2 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-medium fs-13">
                            <i class="ri-list-check-2 me-1 text-primary"></i>Sub-Questions
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="addSubQRow(this.closest('.q-row'))">
                            <i class="ri-add-line me-1"></i>Add Sub-question
                        </button>
                    </div>
                    <div class="sq-container"></div>
                </div>
            </div>
        </div>
    </template>

    @push('scripts')
    <script>
    const ALL_FIELD_TYPES  = @json($fieldTypesForJs);
    const TYPE_OPTIONS_MAP = @json($typeOptions);
    const TYPES_WITH_OPTS  = ['switch', 'option_list'];
    const SUB_Q_TYPE       = 'sub_questionnaire';
    const DATA_TYPES_URL   = "{{ route('admin.master.data-types.index') }}";

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

    function populateFtSelect(sel, warn, type, selectedId) {
        sel.innerHTML = '<option value="">— Select option set —</option>';
        const matching = ALL_FIELD_TYPES.filter(ft => ft.type === type);
        if (matching.length === 0) { sel.disabled = true; warn.style.display = ''; return; }
        warn.style.display = 'none'; sel.disabled = false;
        matching.forEach(ft => {
            const o = document.createElement('option');
            o.value = ft.id; o.textContent = ft.name;
            o.dataset.options = JSON.stringify(ft.options);
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

    // ── Outer row management ──────────────────────────────────────────────────
    function addQRow(prefill) {
        prefill = prefill || {};
        const tmpl  = document.getElementById('qRowTemplate');
        const clone = tmpl.content.cloneNode(true);
        clone.querySelector('.q-type-select').innerHTML = buildTypeOptionsHtml();
        document.getElementById('qRowsContainer').appendChild(clone);
        updateRowNumbers();

        const liveRow = [...document.querySelectorAll('#qRowsContainer .q-row')].at(-1);
        if (prefill.name)   liveRow.querySelector('.q-name').value   = prefill.name;
        if (prefill.key)    liveRow.querySelector('.q-key').value    = prefill.key;
        if (prefill.status) liveRow.querySelector('.q-status').value = prefill.status;
        liveRow.querySelector('.q-enabled-cb').checked  = prefill.enabled  !== '0';
        liveRow.querySelector('.q-required-cb').checked = prefill.required !== '0';

        if (prefill.type) {
            const typeSel = liveRow.querySelector('.q-type-select');
            typeSel.value = prefill.type;
            onQTypeChange(typeSel);
            if (prefill.type !== SUB_Q_TYPE && prefill.field_type_id) {
                liveRow.querySelector('.q-ft-select').value = prefill.field_type_id;
                onQFtChange(liveRow.querySelector('.q-ft-select'));
            }
            if (prefill.type === SUB_Q_TYPE && prefill.parent_id)
                liveRow.querySelector('.q-parent-select').value = prefill.parent_id;
        }
    }

    function removeQRow(btn) { btn.closest('.q-row').remove(); updateRowNumbers(); }

    function updateRowNumbers() {
        const rows = [...document.querySelectorAll('#qRowsContainer .q-row')];
        rows.forEach((row, i) => {
            row.querySelector('.q-row-num').textContent = 'Question #' + (i + 1);
            row.querySelector('.btn-remove-row').style.display = rows.length > 1 ? '' : 'none';
        });
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
            if (!row.querySelector('.sq-row')) addSubQRow(row);
            return;
        }
        stdSection.style.display = '';
        subSection.style.display = 'none';
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

    // ── Sub-question row management ───────────────────────────────────────────
    function addSubQRow(groupRow, prefill) {
        prefill = prefill || {};
        const container = groupRow.querySelector('.sq-container');
        const div = document.createElement('div');
        div.className = 'sq-row border rounded p-2 mb-2 bg-white';
        div.innerHTML = `
            <div class="row g-2 mb-2">
                <div class="col-md-7">
                    <label class="form-label form-label-sm">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm sq-name" maxlength="255" placeholder="Sub-question name">
                </div>
                <div class="col-md-5">
                    <label class="form-label form-label-sm">Key <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm font-monospace sq-key" maxlength="100" placeholder="sub_key">
                </div>
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
                        <i class="ri-alert-line me-1"></i>No option sets.
                        <a href="${DATA_TYPES_URL}" target="_blank">Create in Data Types</a>.
                    </div>
                </div>
            </div>
            <div class="sq-options-preview mb-2 p-2 rounded border bg-light" style="display:none;">
                <small class="text-muted me-1">Options:</small><span class="sq-options-badges"></span>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input sq-enabled-cb" type="checkbox" checked>
                    <label class="form-check-label form-label-sm mb-0">Enabled</label>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input sq-required-cb" type="checkbox" checked>
                    <label class="form-check-label form-label-sm mb-0">Required</label>
                </div>
                <select class="form-select form-select-sm sq-status" style="width:auto;min-width:100px;">
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button type="button" class="btn btn-sm btn-outline-danger ms-auto sq-remove-btn"
                        onclick="removeSqRow(this)" style="display:none;">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>`;
        container.appendChild(div);
        updateSqRemoveBtns(groupRow);

        if (prefill.name)   div.querySelector('.sq-name').value   = prefill.name;
        if (prefill.key)    div.querySelector('.sq-key').value    = prefill.key;
        if (prefill.status) div.querySelector('.sq-status').value = prefill.status;
        if (prefill.enabled  !== undefined) div.querySelector('.sq-enabled-cb').checked  = prefill.enabled  !== '0';
        if (prefill.required !== undefined) div.querySelector('.sq-required-cb').checked = prefill.required !== '0';
        if (prefill.type) {
            const typeSel = div.querySelector('.sq-type-select');
            typeSel.value = prefill.type; onSqTypeChange(typeSel);
            if (prefill.field_type_id) {
                div.querySelector('.sq-ft-select').value = prefill.field_type_id;
                onSqFtChange(div.querySelector('.sq-ft-select'));
            }
        }
    }

    function removeSqRow(btn) {
        const groupRow = btn.closest('.q-row');
        btn.closest('.sq-row').remove();
        updateSqRemoveBtns(groupRow);
    }

    function updateSqRemoveBtns(groupRow) {
        const rows = [...groupRow.querySelectorAll('.sq-row')];
        rows.forEach(r => { r.querySelector('.sq-remove-btn').style.display = rows.length > 1 ? '' : 'none'; });
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

    // ── Pre-submit: flatten rows into hidden inputs ────────────────────────────
    document.getElementById('createMultiForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        form.querySelectorAll('input[type="hidden"][name$="[]"]').forEach(el => el.remove());

        const payload  = [];
        const sectionId = document.getElementById('createSectionId').value;

        document.querySelectorAll('#qRowsContainer .q-row').forEach(row => {
            const type = row.querySelector('.q-type-select').value;
            if (type === SUB_Q_TYPE) {
                const parentId = row.querySelector('.q-parent-select')?.value || '';
                row.querySelectorAll('.sq-row').forEach(sq => {
                    const sqType   = sq.querySelector('.sq-type-select').value;
                    const sqFtWrap = sq.querySelector('.sq-ft-wrap');
                    payload.push({
                        name:          sq.querySelector('.sq-name').value,
                        key:           sq.querySelector('.sq-key').value,
                        type:          sqType,
                        field_type_id: (sqFtWrap && sqFtWrap.style.display !== 'none')
                                       ? (sq.querySelector('.sq-ft-select')?.value || '') : '',
                        section_id:    sectionId,
                        parent_id:     parentId,
                        enabled:       sq.querySelector('.sq-enabled-cb').checked ? '1' : '0',
                        required:      sq.querySelector('.sq-required-cb').checked ? '1' : '0',
                        status:        sq.querySelector('.sq-status').value,
                    });
                });
            } else {
                const ftWrap = row.querySelector('.q-ft-wrap');
                payload.push({
                    name:          row.querySelector('.q-name').value,
                    key:           row.querySelector('.q-key').value,
                    type:          type,
                    field_type_id: (ftWrap && ftWrap.style.display !== 'none')
                                   ? (row.querySelector('.q-ft-select')?.value || '') : '',
                    section_id:    sectionId,
                    parent_id:     '',
                    enabled:       row.querySelector('.q-enabled-cb').checked ? '1' : '0',
                    required:      row.querySelector('.q-required-cb').checked ? '1' : '0',
                    status:        row.querySelector('.q-status').value,
                });
            }
        });

        payload.forEach(d => {
            ['name','key','type','field_type_id','section_id','parent_id','enabled','required','status'].forEach(f => {
                const hi = document.createElement('input');
                hi.type = 'hidden'; hi.name = f + '[]'; hi.value = d[f] || '';
                form.appendChild(hi);
            });
        });

        form.submit();
    });

    // ── Init ──────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->any() && count(old('name', [])) > 0)
        const od = {
            name:     @json(old('name', [])),
            key:      @json(old('key', [])),
            type:     @json(old('type', [])),
            ftId:     @json(old('field_type_id', [])),
            parentId: @json(old('parent_id', [])),
            enabled:  @json(old('enabled', [])),
            required: @json(old('required', [])),
            status:   @json(old('status', [])),
        };
        od.name.forEach((n, i) => addQRow({
            name: n, key: od.key[i], type: od.type[i],
            field_type_id: od.ftId[i], parent_id: od.parentId[i],
            enabled: od.enabled[i], required: od.required[i], status: od.status[i],
        }));
        @else
        addQRow();
        @endif
    });
    </script>
    @endpush
</x-app-layout>
