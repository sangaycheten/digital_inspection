<x-app-layout>
    <x-slot name="title">Edit User</x-slot>

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    @endpush

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Edit User</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                        <li class="breadcrumb-item active">Edit User</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xxl-6 col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <div class="d-flex align-items-center gap-2 flex-grow-1">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary fs-18">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">{{ $user->name }}</h5>
                            <small class="text-muted">{{ $user->email }}</small>
                        </div>
                    </div>
                    @foreach($user->roles as $role)
                        <span class="badge
                            @if($role->name === 'system-administrator') bg-danger-subtle text-danger
                            @elseif($role->name === 'manager') bg-warning-subtle text-warning
                            @elseif($role->name === 'field-technician') bg-primary-subtle text-primary
                            @else bg-success-subtle text-success
                            @endif">
                            {{ role_label($role->name) }}
                        </span>
                    @endforeach
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $user->name) }}"
                                   required autofocus maxlength="255">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $user->email) }}"
                                   required maxlength="255">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role"
                                    required>
                                <option value="" disabled>Select a role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}"
                                        {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>
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
                                    <option value="{{ $client->id }}"
                                        {{ old('client_id', $user->client_id) == $client->id ? 'selected' : '' }}>
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

                                        <div class="mb-4">
                            <label for="timezone" class="form-label">Timezone <span class="text-danger">*</span></label>
                            <select class="form-select @error('timezone') is-invalid @enderror"
                                    id="timezone" name="timezone" required>
                                <option value="">— Select Timezone —</option>
                                @foreach(\DateTimeZone::listIdentifiers() as $tz)
                                    <option value="{{ $tz }}" {{ old('timezone', $user->timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
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
                                <i class="ri-save-line me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Password Management Card --}}
    @can('edit users')
    <div class="row justify-content-center">
        <div class="col-xxl-6 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-lock-password-line me-2 text-warning"></i>Password Management
                    </h5>
                </div>
                <div class="card-body">

                    @if(session('pwd_success'))
                    <div class="alert alert-success alert-border-left alert-dismissible fade show py-2 mb-3" role="alert">
                        <i class="ri-checkbox-circle-line me-2"></i>{{ session('pwd_success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif
                    @if(session('pwd_error'))
                    <div class="alert alert-danger alert-border-left alert-dismissible fade show py-2 mb-3" role="alert">
                        <i class="ri-error-warning-line me-2"></i>{{ session('pwd_error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    {{-- Option 1: Set password manually --}}
                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                        @csrf
                        @method('PUT')

                        <p class="text-muted fs-13 mb-3">Set a new password directly for this user.</p>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control @error('new_password') is-invalid @enderror"
                                       id="new_password" name="new_password"
                                       autocomplete="new-password"
                                       placeholder="Enter new password">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('new_password', this)" tabindex="-1">
                                    <i class="ri-eye-line"></i>
                                </button>
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Min 8 characters with uppercase, lowercase, number, and symbol.</div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control"
                                       id="new_password_confirmation" name="new_password_confirmation"
                                       placeholder="Confirm new password">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('new_password_confirmation', this)" tabindex="-1">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       id="force_password_change" name="force_password_change"
                                       value="1" checked>
                                <label class="form-check-label fs-13" for="force_password_change">
                                    Require user to change this password on next login
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning"
                                onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span> Resetting...'; this.form.submit();">
                            <i class="ri-lock-password-line me-1"></i> Reset Password
                        </button>
                    </form>

                    <hr class="my-4">

                    {{-- Option 2: Send credentials email --}}
                    <p class="text-muted fs-13 mb-2">Or send a new temporary password to the user's email:</p>
                    @if($user->credentials_sent_at)
                    <p class="fs-12 text-muted mb-3">
                        <i class="ri-time-line me-1"></i>Last sent {{ $user->credentials_sent_at->diffForHumans() }}
                        ({{ $user->credentials_sent_at->format('d M Y, H:i') }})
                    </p>
                    @endif
                    <form id="sendCredentialsForm" method="POST" action="{{ route('admin.users.send-credentials', $user) }}">
                        @csrf
                        <button type="button" class="btn btn-outline-info"
                                data-bs-toggle="modal" data-bs-target="#sendCredentialsConfirmModal">
                            <i class="ri-mail-send-line me-1"></i> Send Credentials Email
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- Send Credentials Confirmation Modal --}}
    <div class="modal fade" id="sendCredentialsConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-mail-send-line me-2 text-info"></i>Reset &amp; Send New Credentials
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted fs-13 mb-3">The following email will be sent to the user:</p>

                    <div class="border rounded p-3 bg-light">
                        <div class="mb-2 d-flex gap-2">
                            <span class="text-muted fs-12" style="min-width:60px;">To</span>
                            <span class="fw-medium fs-13">{{ $user->email }}</span>
                        </div>
                        <div class="mb-3 d-flex gap-2">
                            <span class="text-muted fs-12" style="min-width:60px;">Subject</span>
                            <span class="fs-13">Your Login Credentials – {{ config('app.name') }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="fs-13 text-muted mb-2">Email body will include:</div>
                        <ul class="fs-13 mb-0 ps-3">
                            <li>Greeting to <strong>{{ $user->name }}</strong></li>
                            <li>Login email: <span class="font-monospace">{{ $user->email }}</span></li>
                            <li>A <strong>new</strong> auto-generated temporary password</li>
                            <li>Login link to the portal</li>
                        </ul>
                    </div>

                    <div class="alert alert-danger alert-border-left mt-3 mb-0 py-2">
                        <i class="ri-alert-line me-1"></i>
                        <small>This will <strong>replace the user's current password</strong> with a new randomly generated one and require them to set a new one on next login.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info" id="confirmSendCredBtn"
                            onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span> Sending...'; document.getElementById('sendCredentialsForm').submit();">
                        <i class="ri-send-plane-line me-1"></i> Reset &amp; Send
                    </button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
<script>
const allSitesList  = {!! json_encode($sites) !!};
const sitesByClient = {!! json_encode($sites->groupBy('client_id')) !!};
const savedSiteIds  = {!! json_encode(old('site_ids') ?? $userSiteIds) !!};

function onRoleChange(role) {
    const clientField = document.getElementById('clientField');
    const clientSel   = document.getElementById('client_id');
    const siteSearch  = document.getElementById('siteSearch');
    if (role === 'client-user') {
        clientField.style.display = 'block';
        clientSel.required = true;
        siteSearch.style.display = 'none';
        loadClientSites(clientSel.value, savedSiteIds);
    } else if (role === 'field-technician') {
        clientField.style.display = 'none';
        clientSel.required = false;
        siteSearch.style.display = 'block';
        loadAllSites(allSitesList, savedSiteIds);
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
    const sf    = document.getElementById('siteField');
    const con   = document.getElementById('siteCheckboxes');
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
    const ids         = checkedIds.map(String);
    const allChecked  = sites.every(s => ids.includes(String(s.id)));
    const someChecked = sites.some(s => ids.includes(String(s.id)));
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
    const hdr = document.getElementById('grp_' + key);
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
    document.getElementById('client_id').addEventListener('change', e => loadClientSites(e.target.value, savedSiteIds));
    onRoleChange(role);
    if (role === 'client-user' && clientId) loadClientSites(clientId, savedSiteIds);

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
