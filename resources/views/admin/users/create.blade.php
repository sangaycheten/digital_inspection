<x-app-layout>
    <x-slot name="title">Add User</x-slot>

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    @endpush

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
                            <div id="siteSearch" style="display:none;" class="mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="ri-search-line text-muted"></i></span>
                                    <input type="text" id="siteSearchInput" class="form-control border-start-0"
                                           placeholder="Search by client or site name…"
                                           oninput="filterSites(this.value)">
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="filterSites(''); document.getElementById('siteSearchInput').value=''">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="siteCheckboxes"></div>
                            <div id="siteNoResults" class="text-muted fs-12 py-2 text-center" style="display:none;">No sites match your search.</div>
                            <div class="form-text text-muted mt-1">Select one or more sites this user can access.</div>
                        </div>

                        {{-- Password toggle --}}
                        <div class="mb-3 border rounded p-3 bg-light">
                            <div class="form-check form-switch d-flex align-items-center gap-2 mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="set_password" name="set_password" value="1"
                                       {{ old('set_password') ? 'checked' : '' }}
                                       onchange="togglePasswordFields(this.checked)">
                                <label class="form-check-label fw-semibold mb-0" for="set_password">
                                    Set a password now
                                </label>
                            </div>
                            <div id="pwd_hint_off" class="form-text mt-1" @if(old('set_password')) style="display:none" @endif>
                                Leave off to create the account without a password — you can send login credentials later.
                            </div>
                            <div id="pwd_hint_on" class="mt-2" @if(!old('set_password')) style="display:none" @endif>
                                <div class="alert alert-info alert-border-left py-2 mb-0 fs-13">
                                    <i class="ri-mail-send-line me-1"></i>
                                    Credentials email will be sent automatically to the user with this password. The user will be required to change it on first login.
                                </div>
                            </div>
                        </div>

                        <div id="passwordFields" @if(!old('set_password')) style="display:none" @endif>
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label for="password" class="form-label mb-0">Password <span class="text-danger">*</span></label>
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="generatePassword()" tabindex="-1">
                                        <i class="ri-refresh-line me-1"></i> Auto Generate
                                    </button>
                                </div>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password"
                                           placeholder="Enter or auto-generate a password">
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="togglePwd('password', this)" tabindex="-1">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">Min 8 characters with uppercase, lowercase, number, and symbol.</div>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation"
                                           placeholder="Confirm password">
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="togglePwd('password_confirmation', this)" tabindex="-1">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                </div>
                            </div>

                            <div id="generatedPwdAlert" class="alert alert-success alert-border-left py-2 fs-13 mb-3" style="display:none;">
                                <i class="ri-key-2-line me-1"></i>
                                Generated password: <strong id="generatedPwdDisplay" class="font-monospace ms-1"></strong>
                                <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-success"
                                        onclick="copyGeneratedPwd(this)" title="Copy to clipboard">
                                    <i class="ri-clipboard-line"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="timezone" class="form-label">Timezone <span class="text-danger">*</span></label>
                            <select class="form-select @error('timezone') is-invalid @enderror"
                                    id="timezone" name="timezone" required>
                                <option value="">— Select Timezone —</option>
                                @foreach(\DateTimeZone::listIdentifiers() as $tz)
                                    <option value="{{ $tz }}" {{ old('timezone', 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                            @error('timezone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Used to display system event times in the user's local time.</div>
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
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
<script>
const allSitesList  = {!! json_encode($sites) !!};
const sitesByClient = {!! json_encode($sites->groupBy('client_id')) !!};
const oldSiteIds    = {!! json_encode(old('site_ids', [])) !!};

function onRoleChange(role) {
    const clientField = document.getElementById('clientField');
    const clientSel   = document.getElementById('client_id');
    const siteSearch  = document.getElementById('siteSearch');
    if (role === 'client-user') {
        clientField.style.display = 'block';
        clientSel.required = true;
        siteSearch.style.display = 'none';
        loadClientSites(clientSel.value, oldSiteIds);
    } else if (role === 'field-technician') {
        clientField.style.display = 'none';
        clientSel.required = false;
        siteSearch.style.display = 'block';
        loadAllSites(allSitesList, oldSiteIds);
    } else {
        clientField.style.display = 'none';
        document.getElementById('siteField').style.display = 'none';
        siteSearch.style.display = 'none';
        clientSel.required = false;
    }
}

function filterSites(query) {
    const q       = query.trim().toLowerCase();
    const groups  = document.querySelectorAll('#siteCheckboxes > div');
    let   visible = 0;

    groups.forEach(group => {
        const clientLabel = (group.querySelector('.fw-semibold')?.textContent ?? '').toLowerCase();
        const rows        = group.querySelectorAll('.site-row');
        let   groupVisible = 0;

        rows.forEach(row => {
            const siteName = (row.querySelector('.fw-medium')?.textContent ?? '').toLowerCase();
            const matches  = !q || clientLabel.includes(q) || siteName.includes(q);
            row.style.display = matches ? '' : 'none';
            if (matches) groupVisible++;
        });

        group.style.display = groupVisible > 0 ? '' : 'none';
        visible += groupVisible;
    });

    document.getElementById('siteNoResults').style.display = visible === 0 && q ? '' : 'none';
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
                    <span class="small">
                        <span class="fw-medium">${s.name}</span>
                        <span class="d-block text-muted" style="font-size:11px">${s.address}</span>
                    </span>
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

function generatePassword() {
    const upper   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lower   = 'abcdefghijklmnopqrstuvwxyz';
    const digits  = '0123456789';
    const symbols = '!@#$%^*()-_=+[]{}';   // & < > excluded to avoid HTML encoding in emails
    const all     = upper + lower + digits + symbols;

    // Guarantee at least one of each required character type
    let pwd = [
        upper  [Math.floor(Math.random() * upper.length)],
        lower  [Math.floor(Math.random() * lower.length)],
        digits [Math.floor(Math.random() * digits.length)],
        symbols[Math.floor(Math.random() * symbols.length)],
    ];
    for (let i = 4; i < 12; i++) {
        pwd.push(all[Math.floor(Math.random() * all.length)]);
    }
    // Shuffle so the guaranteed chars aren't always at the front
    pwd = pwd.sort(() => Math.random() - 0.5).join('');

    const pwdEl    = document.getElementById('password');
    const confEl   = document.getElementById('password_confirmation');
    const alertEl  = document.getElementById('generatedPwdAlert');
    const displayEl = document.getElementById('generatedPwdDisplay');

    pwdEl.value  = pwd;
    confEl.value = pwd;

    // Show both fields as text so admin can verify
    pwdEl.type  = 'text';
    confEl.type = 'text';
    pwdEl.closest('.input-group').querySelector('button i').className  = 'ri-eye-off-line';
    confEl.closest('.input-group').querySelector('button i').className = 'ri-eye-off-line';

    displayEl.textContent   = pwd;
    alertEl.style.display   = '';
}

function copyGeneratedPwd(btn) {
    const pwd = document.getElementById('generatedPwdDisplay').textContent;
    navigator.clipboard.writeText(pwd).then(() => {
        btn.innerHTML = '<i class="ri-check-line"></i>';
        setTimeout(() => { btn.innerHTML = '<i class="ri-clipboard-line"></i>'; }, 2000);
    });
}

function togglePasswordFields(show) {
    document.getElementById('passwordFields').style.display = show ? '' : 'none';
    document.getElementById('pwd_hint_off').style.display   = show ? 'none' : '';
    document.getElementById('pwd_hint_on').style.display    = show ? '' : 'none';
    if (!show) {
        document.getElementById('password').value = '';
        document.getElementById('password_confirmation').value = '';
        document.getElementById('generatedPwdAlert').style.display = 'none';
        document.getElementById('generatedPwdDisplay').textContent = '';
    }
}

function togglePwd(id, btn) {
    const inp = document.getElementById(id);
    const show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    btn.querySelector('i').className = show ? 'ri-eye-off-line' : 'ri-eye-line';
}

document.addEventListener('DOMContentLoaded', function () {
    new TomSelect('#timezone', { create: false, maxOptions: null });

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
