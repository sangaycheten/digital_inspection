<x-app-layout>
    <x-slot name="title">Add User</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Add User</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                        <li class="breadcrumb-item active">Add User</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xxl-6 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">User Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   required autofocus maxlength="255" placeholder="Enter full name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}"
                                   required maxlength="255" placeholder="Enter email address">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role"
                                    required>
                                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select a role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                        {{ role_label($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="clientField" style="display:none;">
                            <label for="client_id" class="form-label">
                                Client <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id">
                                <option value="">-- Select Client --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }} ({{ $client->custom_client_code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted">Required for Client User role.</div>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="siteField" style="display:none;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label mb-0">
                                    Assign Sites <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex gap-2 align-items-center">
                                    <a href="#" class="text-primary small text-decoration-none" onclick="selectAllSites(event,true)">Select All</a>
                                    <span class="text-muted small">|</span>
                                    <a href="#" class="text-secondary small text-decoration-none" onclick="selectAllSites(event,false)">Clear All</a>
                                </div>
                            </div>
                            <div id="siteIdsError" class="text-danger small mb-1" style="display:none;"></div>
                            @error('site_ids')
                                <div class="text-danger small mb-1">{{ $message }}</div>
                            @enderror
                            <div id="siteCheckboxes"></div>
                            <div class="form-text text-muted mt-1">Select one or more sites this user can access.</div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password"
                                   required placeholder="Enter password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Min 8 characters with uppercase, lowercase, number, and symbol.</div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation"
                                   required placeholder="Confirm password">
                        </div>

                        <div class="hstack gap-2 justify-content-end">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-user-add-line me-1"></i> Create User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
<script>
const allSitesList  = @json($sites);
const sitesByClient = @json($sites->groupBy('client_id'));
const oldSiteIds    = @json(old('site_ids', []));

function onRoleChange(role) {
    const clientField = document.getElementById('clientField');
    const clientSel   = document.getElementById('client_id');
    if (role === 'client-user') {
        clientField.style.display = 'block';
        clientSel.required = true;
        loadClientSites(clientSel.value, oldSiteIds);
    } else if (role === 'field-technician') {
        clientField.style.display = 'none';
        clientSel.required = false;
        loadAllSites(allSitesList, oldSiteIds);
    } else {
        clientField.style.display = 'none';
        document.getElementById('siteField').style.display = 'none';
        clientSel.required = false;
    }
}

function loadClientSites(clientId, checkedIds) {
    const sf   = document.getElementById('siteField');
    const con  = document.getElementById('siteCheckboxes');
    const sites = sitesByClient[clientId] || [];
    if (!clientId || !sites.length) { sf.style.display = 'none'; return; }
    sf.style.display = 'block';
    con.innerHTML = buildGroup('c' + clientId, null, sites, checkedIds);
    applyIndeterminate();
}

function loadAllSites(sites, checkedIds) {
    const sf  = document.getElementById('siteField');
    const con = document.getElementById('siteCheckboxes');
    if (!sites.length) { sf.style.display = 'none'; return; }
    const grouped = {};
    sites.forEach(s => {
        const key   = 'g' + (s.client_id || 0);
        const label = s.client ? s.client.name + ' (' + s.client.custom_client_code + ')' : 'Unknown';
        if (!grouped[key]) grouped[key] = { label, sites: [] };
        grouped[key].sites.push(s);
    });
    sf.style.display = 'block';
    con.innerHTML = Object.entries(grouped).map(([k, g]) => buildGroup(k, g.label, g.sites, checkedIds)).join('');
    applyIndeterminate();
}

function buildGroup(key, clientName, sites, checkedIds) {
    const ids        = checkedIds.map(String);
    const allChecked = sites.every(s => ids.includes(String(s.id)));
    const someChecked= sites.some(s => ids.includes(String(s.id)));
    const header = clientName
        ? `<div class="d-flex align-items-center px-3 py-2 bg-light border-bottom">
               <input type="checkbox" class="form-check-input me-2 flex-shrink-0" id="grp_${key}"
                      ${allChecked ? 'checked' : ''} ${(!allChecked && someChecked) ? 'data-indet="1"' : ''}
                      onchange="toggleGroup(this,'${key}')">
               <label class="fw-semibold small mb-0 text-dark" for="grp_${key}" style="cursor:pointer">${clientName}</label>
           </div>`
        : '';
    const rows = sites.map((s, i) => {
        const chk = ids.includes(String(s.id)) ? 'checked' : '';
        return `<label class="d-flex align-items-center gap-2 px-3 py-2 border-bottom site-row mb-0" style="cursor:pointer">
                    <input type="checkbox" class="form-check-input flex-shrink-0 site-${key}" name="site_ids[]"
                           id="site_${s.id}" value="${s.id}" ${chk} onchange="updateGroupHeader('${key}')">
                    <span class="text-muted small flex-shrink-0">${i + 1}.</span>
                    <span class="small">${s.address}</span>
                </label>`;
    }).join('');
    return `<div class="border rounded mb-2 overflow-hidden">${header}${rows}</div>`;
}

function toggleGroup(cb, key) {
    document.querySelectorAll('.site-' + key).forEach(el => el.checked = cb.checked);
}

function updateGroupHeader(key) {
    const hdr  = document.getElementById('grp_' + key);
    if (!hdr) return;
    const boxes   = document.querySelectorAll('.site-' + key);
    const checked = [...boxes].filter(b => b.checked).length;
    hdr.checked       = checked === boxes.length;
    hdr.indeterminate = checked > 0 && checked < boxes.length;
}

function selectAllSites(e, state) {
    e.preventDefault();
    document.querySelectorAll('#siteCheckboxes input[name="site_ids[]"]').forEach(cb => cb.checked = state);
    document.querySelectorAll('#siteCheckboxes input[id^="grp_"]').forEach(cb => { cb.checked = state; cb.indeterminate = false; });
}

function applyIndeterminate() {
    document.querySelectorAll('[data-indet="1"]').forEach(el => el.indeterminate = true);
}

document.addEventListener('DOMContentLoaded', function () {
    const role     = document.getElementById('role').value;
    const clientId = document.getElementById('client_id').value;
    document.getElementById('role').addEventListener('change', e => onRoleChange(e.target.value));
    document.getElementById('client_id').addEventListener('change', e => loadClientSites(e.target.value, oldSiteIds));
    onRoleChange(role);
    if (role === 'client-user' && clientId) loadClientSites(clientId, oldSiteIds);

    document.querySelector('form').addEventListener('submit', function (e) {
        const currentRole = document.getElementById('role').value;
        const needsSites  = currentRole === 'client-user' || currentRole === 'field-technician';
        if (!needsSites) return;
        const checked = document.querySelectorAll('#siteCheckboxes input[name="site_ids[]"]:checked').length;
        if (checked === 0) {
            e.preventDefault();
            const errEl = document.getElementById('siteIdsError');
            errEl.textContent = 'Please select at least one site.';
            errEl.style.display = 'block';
            document.getElementById('siteField').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
</script>
@endpush
</x-app-layout>
