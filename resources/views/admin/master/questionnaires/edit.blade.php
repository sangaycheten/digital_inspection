<x-app-layout>
    <x-slot name="title">Edit Questionnaire</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Edit Questionnaire</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.master.questionnaires.index') }}">Questionnaires</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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

    @if($subQuestionnaires->isNotEmpty())
    {{-- ── Sub-group edit ────────────────────────────────────────────────── --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-list-check-2 me-2 text-primary"></i>Edit Sub-Questions
                    </h5>
                    <div class="text-muted fs-12 mt-1">
                        {{ $questionnaire->name }} <span class="font-monospace">({{ $questionnaire->key }})</span>
                    </div>
                </div>

                <form method="POST"
                      action="{{ route('admin.master.questionnaires.sub-group.update', $questionnaire) }}"
                      id="editSubGroupForm">
                    @csrf
                    <div class="card-body">

                        <div class="mb-3" style="max-width:460px;">
                            <label class="form-label fw-medium">Section</label>
                            <select name="section_id" id="sgSectionId" class="form-select">
                                <option value="">— No section —</option>
                                @foreach($sections as $sec)
                                <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">All sub-questions will belong to this section.</div>
                        </div>

                        <hr class="my-3">

                        <div id="sgContainer"></div>

                        <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-2"
                                onclick="sgAddRow()">
                            <i class="ri-add-line me-1"></i> Add Sub-question
                        </button>

                    </div>
                    <div class="card-footer d-flex gap-2 justify-content-end">
                        <a href="{{ route('admin.master.questionnaires.index') }}" class="btn btn-light">
                            <i class="ri-arrow-left-line me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Save All Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @else
    {{-- ── Single questionnaire edit ─────────────────────────────────────── --}}
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-edit-line me-2 text-primary"></i>Edit Questionnaire
                    </h5>
                </div>

                <form method="POST"
                      action="{{ route('admin.master.questionnaires.update', $questionnaire) }}"
                      id="editQuestionnaireForm">
                    @csrf @method('PUT')
                    <div class="card-body">

                        {{-- Section --}}
                        <div class="mb-3">
                            <label class="form-label">Section</label>
                            <select name="section_id" class="form-select">
                                <option value="">— No section —</option>
                                @foreach($sections as $sec)
                                <option value="{{ $sec->id }}"
                                    {{ old('section_id', $questionnaire->section_id) === $sec->id ? 'selected' : '' }}>
                                    {{ $sec->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Data Type --}}
                        <div class="mb-3">
                            <label class="form-label">Data Type <span class="text-danger">*</span></label>
                            <select name="type" id="editType" class="form-select" required
                                    onchange="onEditTypeChange(this.value)">
                                @foreach($typeOptions as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('type', $questionnaire->type) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Option Set (switch / option_list only) --}}
                        <div id="editFieldTypeWrap" class="mb-3" style="display:none;">
                            <label class="form-label" id="editFieldTypeLabel">
                                Option Set <span class="text-danger">*</span>
                            </label>
                            <select name="field_type_id" id="editFieldTypeId" class="form-select"
                                    onchange="onEditFieldTypeChange()" disabled>
                                <option value="">— Select option set —</option>
                            </select>
                            <div id="editNoConfigsWarning" class="form-text text-warning" style="display:none;">
                                <i class="ri-alert-line me-1"></i>No option sets of this type.
                                <a href="{{ route('admin.master.data-types.index') }}" target="_blank">Create in Data Types</a>.
                            </div>
                            <div id="editOptionsPreview" class="mt-2" style="display:none;">
                                <div class="p-3 rounded border bg-light">
                                    <p class="text-muted fs-12 mb-2">Available options:</p>
                                    <div class="d-flex flex-wrap gap-2" id="editOptionsBadges"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Name --}}
                        <div class="mb-3">
                            <label class="form-label">Question Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required maxlength="255"
                                   value="{{ old('name', $questionnaire->name) }}">
                        </div>

                        {{-- Key --}}
                        <div class="mb-3">
                            <label class="form-label">Key <span class="text-danger">*</span></label>
                            <input type="text" name="key" class="form-control font-monospace" required maxlength="100"
                                   value="{{ old('key', $questionnaire->key) }}">
                            <div class="form-text">Unique identifier. Letters, numbers, hyphens, underscores. Saved in lowercase.</div>
                        </div>

                        {{-- Parent (sub_questionnaire type only) --}}
                        <div id="editParentWrap" class="mb-3" style="display:none;">
                            <label class="form-label">Parent Questionnaire <span class="text-danger">*</span></label>
                            <select name="parent_id" id="editParentId" class="form-select" disabled>
                                <option value="">— Select parent questionnaire —</option>
                                @foreach($parentQuestionnaires as $pq)
                                <option value="{{ $pq->id }}"
                                    {{ old('parent_id', $questionnaire->parent_id) === $pq->id ? 'selected' : '' }}>
                                    {{ $pq->name }} ({{ $pq->key }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Enabled & Required --}}
                        <div class="mb-3">
                            <label class="form-label d-block">Enabled &amp; Required</label>
                            <div class="d-flex gap-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="enabled"
                                           id="editEnabled" value="1"
                                           {{ old('enabled', $questionnaire->enabled ? '1' : '0') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="editEnabled">
                                        <span class="fw-medium">Enabled</span>
                                        <span class="d-block text-muted fs-12">Visible to Technicians</span>
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="required"
                                           id="editRequired" value="1"
                                           {{ old('required', $questionnaire->required ? '1' : '0') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="editRequired">
                                        <span class="fw-medium">Required</span>
                                        <span class="d-block text-muted fs-12">Answer is mandatory</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active"   {{ old('status', $questionnaire->status) === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $questionnaire->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                    </div>
                    <div class="card-footer d-flex gap-2 justify-content-end">
                        <a href="{{ route('admin.master.questionnaires.index') }}" class="btn btn-light">
                            <i class="ri-arrow-left-line me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
    const ALL_FIELD_TYPES  = @json($fieldTypesForJs);
    const TYPE_OPTIONS_MAP = @json($typeOptions);
    const TYPES_WITH_OPTS  = ['switch', 'option_list'];
    const SUB_Q_TYPE       = 'sub_questionnaire';
    const DATA_TYPES_URL   = "{{ route('admin.master.data-types.index') }}";
    const BASE_URL         = "{{ url('admin/master/questionnaires') }}";

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

    function buildSubTypeOptionsHtml() {
        let html = '<option value="">— Select —</option>';
        for (const [val, lbl] of Object.entries(TYPE_OPTIONS_MAP))
            if (val !== SUB_Q_TYPE) html += `<option value="${val}">${lbl}</option>`;
        return html;
    }

    function sgEsc(str) {
        return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    @if($subQuestionnaires->isNotEmpty())
    // ── Sub-group edit ────────────────────────────────────────────────────────
    function sgAddRow(prefill) {
        prefill = prefill || {};
        const container = document.getElementById('sgContainer');
        const div = document.createElement('div');
        div.className = 'sg-row border rounded p-2 mb-2 bg-white';
        div.innerHTML = `
            <input type="hidden" name="sub_id[]" class="sg-id" value="${sgEsc(prefill.id || '')}">
            <div class="row g-2 mb-2">
                <div class="col-md-7">
                    <label class="form-label form-label-sm">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name[]" class="form-control form-control-sm sg-name"
                           maxlength="255" value="${sgEsc(prefill.name || '')}" placeholder="Sub-question name">
                </div>
                <div class="col-md-5">
                    <label class="form-label form-label-sm">Key <span class="text-danger">*</span></label>
                    <input type="text" name="key[]" class="form-control form-control-sm font-monospace sg-key"
                           maxlength="100" value="${sgEsc(prefill.key || '')}" placeholder="sub_key">
                </div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-md-5">
                    <label class="form-label form-label-sm">Data Type <span class="text-danger">*</span></label>
                    <select name="type[]" class="form-select form-select-sm sg-type-select" onchange="onSgTypeChange(this)">
                        ${buildSubTypeOptionsHtml()}
                    </select>
                </div>
                <div class="col-md-7 sg-ft-wrap" style="display:none;">
                    <label class="form-label form-label-sm sg-ft-label">Option Set <span class="text-danger">*</span></label>
                    <select name="field_type_id[]" class="form-select form-select-sm sg-ft-select"
                            onchange="onSgFtChange(this)" disabled>
                        <option value="">— Select option set —</option>
                    </select>
                    <div class="sg-no-configs form-text text-warning" style="display:none;">
                        <i class="ri-alert-line me-1"></i>No option sets.
                        <a href="${DATA_TYPES_URL}" target="_blank">Create in Data Types</a>.
                    </div>
                </div>
            </div>
            <div class="sg-options-preview mb-2 p-2 rounded border bg-light" style="display:none;">
                <small class="text-muted me-1">Options:</small><span class="sg-options-badges"></span>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input sg-enabled-cb" type="checkbox" ${prefill.enabled !== '0' ? 'checked' : ''}>
                    <label class="form-check-label form-label-sm mb-0">Enabled</label>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input sg-required-cb" type="checkbox" ${prefill.id ? (prefill.required === '1' ? 'checked' : '') : 'checked'}>
                    <label class="form-check-label form-label-sm mb-0">Required</label>
                </div>
                <select name="status[]" class="form-select form-select-sm sg-status" style="width:auto;min-width:100px;">
                    <option value="active"   ${(prefill.status || 'active') === 'active'   ? 'selected' : ''}>Active</option>
                    <option value="inactive" ${prefill.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                </select>
                <button type="button" class="btn btn-sm btn-outline-danger ms-auto sg-remove-btn"
                        onclick="sgRemoveRow(this)">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>`;
        container.appendChild(div);
        sgUpdateRemoveBtns();

        if (prefill.type) {
            const typeSel = div.querySelector('.sg-type-select');
            typeSel.value = prefill.type;
            onSgTypeChange(typeSel);
            if (prefill.field_type_id) {
                div.querySelector('.sg-ft-select').value = prefill.field_type_id;
                onSgFtChange(div.querySelector('.sg-ft-select'));
            }
        }
    }

    function sgRemoveRow(btn) {
        const row   = btn.closest('.sg-row');
        const subId = row.querySelector('.sg-id')?.value;
        if (!subId) { row.remove(); sgUpdateRemoveBtns(); return; }
        if (!confirm('Delete this sub-question? This can be undone by an administrator.')) return;
        btn.disabled = true;
        const csrf = document.querySelector('#editSubGroupForm input[name="_token"]').value;
        fetch(`${BASE_URL}/${subId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => { if (!r.ok) throw new Error(); row.remove(); sgUpdateRemoveBtns(); })
        .catch(() => { btn.disabled = false; alert('Failed to delete. Please try again.'); });
    }

    function sgUpdateRemoveBtns() {
        const rows = [...document.querySelectorAll('#sgContainer .sg-row')];
        rows.forEach(r => { r.querySelector('.sg-remove-btn').style.display = rows.length > 1 ? '' : 'none'; });
    }

    function onSgTypeChange(selectEl) {
        const row    = selectEl.closest('.sg-row');
        const type   = selectEl.value;
        const ftWrap = row.querySelector('.sg-ft-wrap');
        const ftSel  = row.querySelector('.sg-ft-select');
        const warn   = row.querySelector('.sg-no-configs');
        const prev   = row.querySelector('.sg-options-preview');
        const label  = row.querySelector('.sg-ft-label');
        prev.style.display = 'none';
        ftSel.innerHTML = '<option value="">— Select option set —</option>';
        ftSel.disabled = true; ftWrap.style.display = 'none';
        if (!TYPES_WITH_OPTS.includes(type)) return;
        ftWrap.style.display = '';
        label.innerHTML = (type === 'switch' ? 'Switch Option Set' : 'Option List Set')
                         + ' <span class="text-danger">*</span>';
        populateFtSelect(ftSel, warn, type, null);
    }

    function onSgFtChange(selectEl) {
        const row = selectEl.closest('.sg-row');
        showOptionBadges(selectEl, row.querySelector('.sg-options-preview'), row.querySelector('.sg-options-badges'));
    }

    document.getElementById('editSubGroupForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;

        // Remove stale injected inputs from a previous submit attempt
        form.querySelectorAll('input[type="hidden"].sg-injected').forEach(el => el.remove());

        document.querySelectorAll('#sgContainer .sg-row').forEach(row => {
            const inject = (name, value) => {
                const hi = document.createElement('input');
                hi.type = 'hidden'; hi.name = name; hi.className = 'sg-injected';
                hi.value = value;
                row.appendChild(hi);
            };
            inject('enabled[]',  row.querySelector('.sg-enabled-cb').checked  ? '1' : '0');
            inject('required[]', row.querySelector('.sg-required-cb').checked ? '1' : '0');
            // Inject field_type_id explicitly so disabled selects don't break array alignment
            const ftWrap = row.querySelector('.sg-ft-wrap');
            const ftSel  = row.querySelector('.sg-ft-select');
            inject('field_type_id[]', (ftWrap && ftWrap.style.display !== 'none') ? (ftSel?.value || '') : '');
        });

        // Strip name from the actual selects so they don't double-submit
        document.querySelectorAll('#sgContainer .sg-ft-select').forEach(sel => sel.removeAttribute('name'));

        form.submit();
    });

    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->any() && count(old('name', [])) > 0)
        const od = {
            subId:    @json(old('sub_id', [])),
            name:     @json(old('name', [])),
            key:      @json(old('key', [])),
            type:     @json(old('type', [])),
            ftId:     @json(old('field_type_id', [])),
            enabled:  @json(old('enabled', [])),
            required: @json(old('required', [])),
            status:   @json(old('status', [])),
        };
        od.name.forEach((n, i) => sgAddRow({
            id: od.subId[i] || '', name: n, key: od.key[i],
            type: od.type[i], field_type_id: od.ftId[i],
            enabled:  od.enabled[i]  !== undefined ? od.enabled[i]  : '1',
            required: od.required[i] !== undefined ? od.required[i] : '1',
            status:   od.status[i]   || 'active',
        }));
        document.getElementById('sgSectionId').value = @json(old('section_id', ''));
        @else
        const subs = @json($subsForJs);
        if (subs.length === 0) { sgAddRow(); }
        else {
            subs.forEach(sub => sgAddRow(sub));
            document.getElementById('sgSectionId').value = subs[0].section_id || '';
        }
        @endif
    });

    @else
    // ── Single edit ───────────────────────────────────────────────────────────
    function onEditTypeChange(type, selectedId, parentId) {
        selectedId = selectedId || null; parentId = parentId || null;
        const ftWrap     = document.getElementById('editFieldTypeWrap');
        const ftSel      = document.getElementById('editFieldTypeId');
        const label      = document.getElementById('editFieldTypeLabel');
        const parentWrap = document.getElementById('editParentWrap');
        const parentSel  = document.getElementById('editParentId');

        document.getElementById('editOptionsPreview').style.display = 'none';
        ftWrap.style.display = 'none'; ftSel.disabled = true;
        parentWrap.style.display = 'none'; parentSel.disabled = true;

        if (type === SUB_Q_TYPE) {
            parentWrap.style.display = ''; parentSel.disabled = false;
            if (parentId) parentSel.value = parentId;
            return;
        }
        if (!TYPES_WITH_OPTS.includes(type)) return;

        ftWrap.style.display = '';
        label.innerHTML = (type === 'switch' ? 'Switch Option Set' : 'Option List Set')
                         + ' <span class="text-danger">*</span>';
        populateFtSelect(ftSel, document.getElementById('editNoConfigsWarning'), type, selectedId);
        if (selectedId) showOptionBadges(ftSel,
            document.getElementById('editOptionsPreview'),
            document.getElementById('editOptionsBadges'));
    }

    function onEditFieldTypeChange() {
        showOptionBadges(
            document.getElementById('editFieldTypeId'),
            document.getElementById('editOptionsPreview'),
            document.getElementById('editOptionsBadges'));
    }

    document.addEventListener('DOMContentLoaded', function() {
        const currentType  = @json(old('type', $questionnaire->type));
        const currentFtId  = @json(old('field_type_id', $questionnaire->field_type_id ?? ''));
        const currentPId   = @json(old('parent_id', $questionnaire->parent_id ?? ''));
        onEditTypeChange(currentType, currentFtId || null, currentPId || null);
    });
    @endif
    </script>
    @endpush
</x-app-layout>
