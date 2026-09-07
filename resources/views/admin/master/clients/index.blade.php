@use('Illuminate\Support\Facades\Storage')
<x-app-layout>
    <x-slot name="title">Clients</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Clients</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item active">Clients</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible alert-border-left fade show" role="alert">
        <i class="ri-checkbox-circle-line me-3 align-middle fs-16"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-building-2-line me-2 text-primary"></i>All Clients
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $clients->total() }}</span>
                    </h5>
                    @can('add clients')
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createClientModal">
                        <i class="ri-add-line me-1"></i> Add Client
                    </button>
                    @endcan
                </div>

                {{-- Filters --}}
                <div class="card-body border-bottom pb-3">
                    <form method="GET" action="{{ route('admin.master.clients.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label text-muted fs-12 mb-1">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search by name or client code..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-search-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.master.clients.index') }}" class="btn btn-light btn-sm ms-1">
                                <i class="ri-refresh-line"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th style="width:48px;"></th>
                                    <th>Client Name</th>
                                    <th>Code</th>
                                    <th>Email</th>
                                    <th>Billing Contact</th>
                                    <th>Manager</th>
                                    <th>Sites</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clients as $client)
                                <tr>
                                    <td class="ps-3 text-muted fs-12">{{ $clients->firstItem() + $loop->index }}</td>
                                    <td>
                                        @if($client->logo)
                                        <img src="{{ Storage::disk('public')->url($client->logo) }}"
                                             alt="{{ $client->name }}"
                                             class="rounded border"
                                             style="width:36px;height:36px;object-fit:contain;background:#f8f9fa;padding:2px;">
                                        @else
                                        <span class="avatar-title rounded bg-primary-subtle text-primary fs-16 d-inline-flex align-items-center justify-content-center"
                                              style="width:36px;height:36px;">
                                            <i class="ri-building-2-line"></i>
                                        </span>
                                        @endif
                                    </td>
                                    <td class="fw-medium">{{ $client->name }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $client->custom_client_code }}</span></td>
                                    <td class="text-muted fs-12">{{ $client->email ?? '—' }}</td>
                                    <td class="text-muted fs-12">{{ Str::limit($client->billing_contact_info, 40) ?? '—' }}</td>
                                    <td class="text-muted fs-12">{{ $client->manager?->name ?? '—' }}</td>
                                    <td><span class="badge bg-primary-subtle text-primary">{{ $client->sites_count }}</span></td>
                                    <td>
                                        @if($client->status === 'active')
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-muted fs-12">{{ $client->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="hstack gap-1">
                                            @can('edit clients')
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#editClientModal{{ $client->id }}">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            @endcan
                                            @can('delete clients')
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal" data-bs-target="#deleteClientModal{{ $client->id }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan
                                        </div>

                                        {{-- Edit Modal --}}
                                        <div class="modal fade" id="editClientModal{{ $client->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Client</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="{{ route('admin.master.clients.update', $client) }}"
                                                          enctype="multipart/form-data">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body">

                                                            {{-- Logo --}}
                                                            <div class="mb-3">
                                                                <label class="form-label">Client Logo</label>
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <img id="editLogoPreview{{ $client->id }}"
                                                                         src="{{ $client->logo ? Storage::disk('public')->url($client->logo) : '' }}"
                                                                         alt="Logo"
                                                                         class="rounded border"
                                                                         style="width:64px;height:64px;object-fit:contain;background:#f8f9fa;padding:4px;{{ $client->logo ? '' : 'display:none;' }}">
                                                                    <div class="flex-grow-1">
                                                                        <input type="file"
                                                                               name="logo"
                                                                               class="form-control form-control-sm"
                                                                               accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                                                               onchange="previewLogo(this, 'editLogoPreview{{ $client->id }}')">
                                                                        <div class="form-text">Leave blank to keep current logo. JPG, PNG, WebP or SVG · max 2 MB</div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                                                <input type="text" name="name" class="form-control" value="{{ $client->name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Email</label>
                                                                <input type="email" name="email" class="form-control" value="{{ $client->email }}" placeholder="contact@company.com">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Client Code <span class="text-danger">*</span></label>
                                                                <input type="text" name="custom_client_code" class="form-control" value="{{ $client->custom_client_code }}" required maxlength="20">
                                                                <div class="form-text">Short unique code, e.g. RPH, WST</div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Billing Contact Info</label>
                                                                <textarea name="billing_contact_info" class="form-control" rows="3">{{ $client->billing_contact_info }}</textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Manager</label>
                                                                <select name="manager_id" class="form-select">
                                                                    <option value="">— No Manager —</option>
                                                                    @foreach($managers as $manager)
                                                                    <option value="{{ $manager->id }}"
                                                                        {{ $client->manager_id === $manager->id ? 'selected' : '' }}>
                                                                        {{ $manager->name }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                                                <select name="status" class="form-select" required>
                                                                    <option value="active"   {{ $client->status === 'active'   ? 'selected' : '' }}>Active</option>
                                                                    <option value="inactive" {{ $client->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Save</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Delete Modal --}}
                                        <div class="modal fade" id="deleteClientModal{{ $client->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="avatar-sm mx-auto mb-3">
                                                            <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-22">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </span>
                                                        </div>
                                                        <h5 class="mb-3">Delete Client</h5>
                                                        <p class="text-muted mb-4">Are you sure you want to delete <strong>{{ $client->name }}</strong>?</p>
                                                        <div class="hstack gap-2 justify-content-center">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST" action="{{ route('admin.master.clients.destroy', $client) }}">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-5">
                                        <i class="ri-building-2-line fs-24 d-block mb-2"></i>No clients found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($clients->hasPages())
                <div class="card-footer">{{ $clients->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createClientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-building-2-line me-2"></i>Add New Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.master.clients.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">

                        {{-- Client details section --}}
                        <h6 class="text-muted text-uppercase fs-11 fw-semibold mb-3">Client Details</h6>

                        {{-- Row 1: Name + Code --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Client Code <span class="text-danger">*</span></label>
                                <input type="text" name="custom_client_code"
                                       class="form-control @error('custom_client_code') is-invalid @enderror"
                                       value="{{ old('custom_client_code') }}" required maxlength="20" placeholder="e.g. RPH">
                                <div class="form-text">Short unique code.</div>
                                @error('custom_client_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Row 2: Email + Status --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" placeholder="contact@company.com">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="active"   {{ old('status', 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        {{-- Row 3: Manager --}}
                        <div class="mb-3">
                            <label class="form-label">Manager</label>
                            <select name="manager_id" class="form-select">
                                <option value="">— No Manager —</option>
                                @foreach($managers as $manager)
                                <option value="{{ $manager->id }}"
                                    {{ old('manager_id') === $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('manager_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Row 4: Logo + Billing side by side --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label">Client Logo</label>
                                <input type="file" name="logo" id="createLogoInput"
                                       class="form-control @error('logo') is-invalid @enderror"
                                       accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                       onchange="previewLogo(this, 'createLogoPreview')">
                                <div class="form-text">JPG, PNG, WebP or SVG · max 2 MB</div>
                                @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <img id="createLogoPreview" alt="Logo Preview" class="rounded border mt-2"
                                     style="width:56px;height:56px;object-fit:contain;background:#f8f9fa;padding:4px;display:none;">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label">Billing Contact Info</label>
                                <textarea name="billing_contact_info" class="form-control" rows="3"
                                          placeholder="Contact name, email, phone...">{{ old('billing_contact_info') }}</textarea>
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- First site section --}}
                        <h6 class="text-muted text-uppercase fs-11 fw-semibold mb-3">
                            <i class="ri-map-pin-line me-1"></i>First Site <span class="text-danger">*</span>
                        </h6>

                        <div class="mb-3">
                            <label class="form-label">Site Name <span class="text-danger">*</span></label>
                            <input type="text" name="site_name" class="form-control @error('site_name') is-invalid @enderror"
                                   value="{{ old('site_name') }}" required>
                            @error('site_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <div class="position-relative">
                                <textarea name="site_address" id="clientSiteAddress"
                                          class="form-control" rows="2" autocomplete="off"
                                          data-suggestions="clientSiteSuggestions"
                                          data-map-id="clientSiteMap"
                                          placeholder="Start typing to search address...">{{ old('site_address') }}</textarea>
                                <ul class="address-suggestions" id="clientSiteSuggestions"></ul>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-flex align-items-center gap-2">
                                <i class="ri-map-2-line text-primary"></i> Pick Location on Map
                                <small class="text-muted fw-normal">(click map to place pin)</small>
                            </label>
                            <div id="clientSiteMap" class="site-map"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Coordinates</label>
                            <div class="input-group">
                                <span class="input-group-text text-muted fs-12">Lat</span>
                                <input type="text" name="site_latitude" id="clientSiteLat"
                                       class="form-control form-control-sm"
                                       value="{{ old('site_latitude') }}" placeholder="e.g. -33.8688">
                                <span class="input-group-text text-muted fs-12">Lng</span>
                                <input type="text" name="site_longitude" id="clientSiteLng"
                                       class="form-control form-control-sm"
                                       value="{{ old('site_longitude') }}" placeholder="e.g. 151.2093">
                                <button type="button" class="btn btn-outline-primary"
                                        data-map-id="clientSiteMap" data-lat-id="clientSiteLat" data-lng-id="clientSiteLng"
                                        onclick="clientMapGoTo(this)">
                                    <i class="ri-crosshair-2-line"></i> Go
                                </button>
                            </div>
                            <small class="text-muted">Or click directly on the map above to place a pin.</small>
                        </div>

                        <div class="mb-1">
                            <input type="hidden" name="site_timezone" id="clientSiteTimezone" value="{{ old('site_timezone', 'UTC') }}">
                            <div class="d-flex align-items-center gap-2 fs-12 text-muted">
                                <i class="ri-time-zone-line"></i> Timezone:
                                <span id="clientSiteTimezoneDisplay" class="badge bg-secondary-subtle text-secondary fs-12">
                                    {{ old('site_timezone', 'UTC') }}
                                </span>
                                <span id="clientSiteTimezoneLoading" class="text-muted fs-11" style="display:none;">
                                    <i class="ri-loader-4-line"></i> Detecting...
                                </span>
                            </div>
                            <div class="form-text">Auto-detected when you place a pin on the map.</div>
                            @error('site_timezone')<div class="text-danger fs-12">{{ $message }}</div>@enderror
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="ri-add-line me-1"></i> Create Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/leaflet/leaflet.css') }}">
    <style>
        .site-map { height: 260px; width: 100%; border-radius: 6px; border: 1px solid #dee2e6; }
        .address-suggestions {
            display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 9999;
            background: #fff; border: 1px solid #dee2e6; border-top: none;
            border-radius: 0 0 6px 6px; max-height: 200px; overflow-y: auto;
            margin: 0; padding: 0; list-style: none; box-shadow: 0 4px 12px rgba(0,0,0,.1);
        }
        .address-suggestions .suggestion-item {
            padding: 8px 12px; font-size: 13px; cursor: pointer;
            border-bottom: 1px solid #f1f1f1; line-height: 1.4;
        }
        .address-suggestions .suggestion-item:last-child { border-bottom: none; }
        .address-suggestions .suggestion-item:hover,
        .address-suggestions .suggestion-item.active { background: #f0f4ff; color: #3d5ee1; }
        .suggestion-searching { padding: 10px 12px; font-size: 12px; color: #6c757d; }
    </style>
    @endpush

    @push('scripts')
    <script src="{{ asset('assets/libs/leaflet/leaflet.js') }}"></script>
    <script>
    function previewLogo(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { preview.src = e.target.result; preview.style.display = ''; };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ── Client site map ───────────────────────────────────────────
    const CLIENT_OSM_TILES = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    const CLIENT_OSM_ATTR  = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
    let clientSiteMap = null, clientSiteMarker = null;

    function clientMapFetchTimezone(lat, lng) {
        const input   = document.getElementById('clientSiteTimezone');
        const display = document.getElementById('clientSiteTimezoneDisplay');
        const loading = document.getElementById('clientSiteTimezoneLoading');
        loading.style.display = '';
        display.className = 'badge bg-secondary-subtle text-secondary fs-12';
        fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lng}&timezone=auto&forecast_days=0`)
            .then(r => r.json())
            .then(data => {
                const tz = data.timezone || 'UTC';
                input.value = tz; display.textContent = tz;
                display.className = 'badge bg-success-subtle text-success fs-12';
            })
            .catch(() => { display.className = 'badge bg-warning-subtle text-warning fs-12'; })
            .finally(() => { loading.style.display = 'none'; });
    }

    function clientMapReverseGeocode(lat, lng) {
        const addrEl = document.getElementById('clientSiteAddress');
        if (!addrEl) return;
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
            headers: { 'Accept-Language': 'en' }
        })
        .then(r => r.json())
        .then(data => { if (data && data.display_name) addrEl.value = data.display_name; })
        .catch(() => {});
    }

    function clientMapPlacePin(lat, lng, skipGeocode) {
        if (clientSiteMarker) clientSiteMap.removeLayer(clientSiteMarker);
        clientSiteMarker = L.marker([lat, lng]).addTo(clientSiteMap);
        clientSiteMap.setView([lat, lng], 17);
        document.getElementById('clientSiteLat').value = parseFloat(lat).toFixed(7);
        document.getElementById('clientSiteLng').value = parseFloat(lng).toFixed(7);
        clientMapFetchTimezone(lat, lng);
        if (!skipGeocode) clientMapReverseGeocode(lat, lng);
    }

    function clientMapGoTo(btn) {
        const lat = parseFloat(document.getElementById('clientSiteLat').value);
        const lng = parseFloat(document.getElementById('clientSiteLng').value);
        if (isNaN(lat) || isNaN(lng)) { alert('Please enter valid latitude and longitude.'); return; }
        clientMapPlacePin(lat, lng, true);
    }

    // Address autocomplete
    function debounceClient(fn, ms) { let t; return function() { clearTimeout(t); t = setTimeout(fn, ms); }; }
    function clientHideSuggestions(ul) { ul.innerHTML = ''; ul.style.display = 'none'; }
    function clientRenderSuggestions(results, ul, ta) {
        ul.innerHTML = '';
        if (!results.length) {
            const li = document.createElement('li');
            li.className = 'suggestion-searching text-muted'; li.textContent = 'No results found.';
            ul.appendChild(li); ul.style.display = 'block'; return;
        }
        results.forEach(r => {
            const li = document.createElement('li');
            li.className = 'suggestion-item';
            li.innerHTML = '<i class="ri-map-pin-line me-2 text-primary"></i>' + r.display_name;
            li.addEventListener('mousedown', e => {
                e.preventDefault(); ta.value = r.display_name; clientHideSuggestions(ul);
                clientMapPlacePin(parseFloat(r.lat), parseFloat(r.lon), true);
            });
            ul.appendChild(li);
        });
        ul.style.display = 'block';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const ta = document.getElementById('clientSiteAddress');
        const ul = document.getElementById('clientSiteSuggestions');
        if (ta && ul) {
            const doSearch = debounceClient(function() {
                const q = ta.value.trim();
                if (q.length < 3) { clientHideSuggestions(ul); return; }
                ul.innerHTML = '<li class="suggestion-searching"><i class="ri-loader-4-line me-1"></i>Searching...</li>';
                ul.style.display = 'block';
                fetch('https://nominatim.openstreetmap.org/search?format=json&limit=5&q=' + encodeURIComponent(q), {
                    headers: { 'Accept-Language': 'en' }
                })
                .then(r => r.json())
                .then(results => clientRenderSuggestions(results, ul, ta))
                .catch(() => clientHideSuggestions(ul));
            }, 500);
            ta.addEventListener('input', doSearch);
            ta.addEventListener('blur', () => setTimeout(() => clientHideSuggestions(ul), 150));
            ta.addEventListener('keydown', e => {
                const items = ul.querySelectorAll('.suggestion-item');
                const active = ul.querySelector('.suggestion-item.active');
                if (!items.length) return;
                if (e.key === 'ArrowDown') { e.preventDefault(); if (!active) items[0].classList.add('active'); else { active.classList.remove('active'); (active.nextElementSibling || items[0]).classList.add('active'); } }
                else if (e.key === 'ArrowUp') { e.preventDefault(); if (active) { active.classList.remove('active'); (active.previousElementSibling || items[items.length-1]).classList.add('active'); } }
                else if (e.key === 'Enter') { const sel = ul.querySelector('.suggestion-item.active'); if (sel) { e.preventDefault(); sel.dispatchEvent(new MouseEvent('mousedown')); } }
                else if (e.key === 'Escape') clientHideSuggestions(ul);
            });
        }

        // Init map when modal opens
        document.getElementById('createClientModal').addEventListener('shown.bs.modal', function() {
            if (clientSiteMap) { clientSiteMap.invalidateSize(); return; }
            clientSiteMap = L.map('clientSiteMap').setView([-25.2744, 133.7751], 4);
            L.tileLayer(CLIENT_OSM_TILES, { attribution: CLIENT_OSM_ATTR, maxZoom: 19 }).addTo(clientSiteMap);
            clientSiteMap.on('click', e => clientMapPlacePin(e.latlng.lat, e.latlng.lng));
            ['clientSiteLat', 'clientSiteLng'].forEach(id => {
                document.getElementById(id).addEventListener('keydown', e => {
                    if (e.key !== 'Enter') return;
                    e.preventDefault();
                    const la = parseFloat(document.getElementById('clientSiteLat').value);
                    const lo = parseFloat(document.getElementById('clientSiteLng').value);
                    if (!isNaN(la) && !isNaN(lo)) clientMapPlacePin(la, lo, true);
                });
            });
        });

        @if($errors->hasAny(['name', 'email', 'custom_client_code', 'status', 'logo', 'manager_id', 'site_name', 'site_timezone']))
        new bootstrap.Modal(document.getElementById('createClientModal')).show();
        @endif
    });
    </script>
    @endpush

</x-app-layout>
