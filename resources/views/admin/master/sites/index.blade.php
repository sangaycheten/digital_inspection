<x-app-layout>
    <x-slot name="title">Sites</x-slot>

    @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/leaflet/leaflet.css') }}">
    <style>
        .site-map { height: 280px; width: 100%; border-radius: 6px; border: 1px solid #dee2e6; }
        .modal-dialog-map { max-width: 640px; }
        .address-suggestions {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 9999;
            background: #fff;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 6px 6px;
            max-height: 220px;
            overflow-y: auto;
            margin: 0;
            padding: 0;
            list-style: none;
            box-shadow: 0 4px 12px rgba(0,0,0,.1);
        }
        .address-suggestions .suggestion-item {
            padding: 8px 12px;
            font-size: 13px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
            line-height: 1.4;
        }
        .address-suggestions .suggestion-item:last-child { border-bottom: none; }
        .address-suggestions .suggestion-item:hover,
        .address-suggestions .suggestion-item.active { background: #f0f4ff; color: #3d5ee1; }
        .suggestion-searching {
            padding: 10px 12px;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
    @endpush

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Sites</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item active">Sites</li>
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
                        <i class="ri-map-pin-line me-2 text-primary"></i>All Sites
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $sites->total() }}</span>
                    </h5>
                    @if($clients->isNotEmpty())
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createSiteModal">
                        <i class="ri-add-line me-1"></i> Add Site
                    </button>
                    @endif
                </div>

                <div class="card-body border-bottom pb-3">
                    <form method="GET" action="{{ route('admin.master.sites.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label text-muted fs-12 mb-1">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search by name or address..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted fs-12 mb-1">Client</label>
                            <select name="client_id" class="form-select form-select-sm">
                                <option value="">All Clients</option>
                                @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i> Filter</button>
                            <a href="{{ route('admin.master.sites.index') }}" class="btn btn-light btn-sm ms-1"><i class="ri-refresh-line"></i> Reset</a>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Client</th>
                                    <th>Site Name</th>
                                    <th>Address</th>
                                    <th>Location</th>
                                    <th>Site Notes</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sites as $site)
                                <tr>
                                    <td class="ps-3 text-muted fs-12">{{ $sites->firstItem() + $loop->index }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">{{ $site->client->custom_client_code ?? '—' }}</span>
                                        <span class="ms-1 fs-13">{{ $site->client->name ?? '—' }}</span>
                                    </td>
                                    <td class="fw-medium">{{ $site->name ?? '—' }}</td>
                                    <td>{{ $site->address }}</td>
                                    <td>
                                        @if($site->latitude && $site->longitude)
                                        <a href="https://www.google.com/maps?q={{ $site->latitude }},{{ $site->longitude }}"
                                           target="_blank" class="text-decoration-none" title="{{ $site->latitude }}, {{ $site->longitude }}">
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="ri-map-pin-2-line me-1"></i>View Map
                                            </span>
                                        </a>
                                        @else
                                        <span class="text-muted fs-12">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted fs-12">{{ Str::limit($site->site_notes, 50) ?? '—' }}</td>
                                    <td class="text-muted fs-12">{{ $site->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="hstack gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#editSiteModal{{ $site->id }}">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal" data-bs-target="#deleteSiteModal{{ $site->id }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>

                                        {{-- Edit Modal --}}
                                        <div class="modal fade" id="editSiteModal{{ $site->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-dialog-map">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="ri-map-pin-line me-2"></i>Edit Site</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="{{ route('admin.master.sites.update', $site) }}">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Client <span class="text-danger">*</span></label>
                                                                    <select name="client_id" class="form-select" required>
                                                                        @foreach($clients as $client)
                                                                        <option value="{{ $client->id }}" {{ $site->client_id == $client->id ? 'selected' : '' }}>
                                                                            {{ $client->name }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Site Name</label>
                                                                    <input type="text" name="name" class="form-control" value="{{ $site->name }}" placeholder="e.g. HQ Building">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                                                    <div class="position-relative">
                                                                        <textarea name="address" class="form-control" rows="2" required
                                                                                  autocomplete="off"
                                                                                  data-suggestions="editSuggestions{{ $site->id }}"
                                                                                  data-map-id="editMap{{ $site->id }}"
                                                                                  placeholder="Start typing to search address...">{{ $site->address }}</textarea>
                                                                        <ul class="address-suggestions" id="editSuggestions{{ $site->id }}"></ul>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label d-flex align-items-center gap-2">
                                                                        <i class="ri-map-2-line text-primary"></i> Pick Location on Map
                                                                        <small class="text-muted fw-normal">(click map to place pin)</small>
                                                                    </label>
                                                                    <div id="editMap{{ $site->id }}" class="site-map"></div>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label">Coordinates</label>
                                                                    <div class="input-group">
                                                                        <span class="input-group-text text-muted fs-12">Lat</span>
                                                                        <input type="text" name="latitude" id="editLat{{ $site->id }}"
                                                                               class="form-control form-control-sm"
                                                                               value="{{ $site->latitude }}" placeholder="e.g. 27.4716">
                                                                        <span class="input-group-text text-muted fs-12">Lng</span>
                                                                        <input type="text" name="longitude" id="editLng{{ $site->id }}"
                                                                               class="form-control form-control-sm"
                                                                               value="{{ $site->longitude }}" placeholder="e.g. 89.6386">
                                                                        <button type="button" class="btn btn-outline-primary btn-go-location"
                                                                                data-map-id="editMap{{ $site->id }}"
                                                                                data-lat-id="editLat{{ $site->id }}"
                                                                                data-lng-id="editLng{{ $site->id }}"
                                                                                title="Go to this location on map">
                                                                            <i class="ri-crosshair-2-line"></i> Go
                                                                        </button>
                                                                    </div>
                                                                    <small class="text-muted">Or click directly on the map above to place a pin.</small>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label">Site Notes</label>
                                                                    <textarea name="site_notes" class="form-control" rows="2">{{ $site->site_notes }}</textarea>
                                                                </div>
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
                                        <div class="modal fade" id="deleteSiteModal{{ $site->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="avatar-sm mx-auto mb-3">
                                                            <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-22">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </span>
                                                        </div>
                                                        <h5 class="mb-3">Delete Site</h5>
                                                        <p class="text-muted mb-4">Are you sure you want to delete this site? All buildings under it will also be removed.</p>
                                                        <div class="hstack gap-2 justify-content-center">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST" action="{{ route('admin.master.sites.destroy', $site) }}">
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
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="ri-map-pin-line fs-24 d-block mb-2"></i>No sites found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($sites->hasPages())
                <div class="card-footer">{{ $sites->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createSiteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-map">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-map-pin-line me-2"></i>Add New Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.master.sites.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Client <span class="text-danger">*</span></label>
                                <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                                    <option value="">-- Select Client --</option>
                                    @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Site Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. HQ Building">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <textarea name="address" id="createAddress"
                                              class="form-control @error('address') is-invalid @enderror"
                                              rows="2" required autocomplete="off"
                                              data-suggestions="createSuggestions"
                                              data-map-id="createMap"
                                              placeholder="Start typing to search address...">{{ old('address') }}</textarea>
                                    <ul class="address-suggestions" id="createSuggestions"></ul>
                                </div>
                                @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label d-flex align-items-center gap-2">
                                    <i class="ri-map-2-line text-primary"></i> Pick Location on Map
                                    <small class="text-muted fw-normal">(click map to place pin)</small>
                                </label>
                                <div id="createMap" class="site-map"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Coordinates</label>
                                <div class="input-group">
                                    <span class="input-group-text text-muted fs-12">Lat</span>
                                    <input type="text" name="latitude" id="createLat"
                                           class="form-control form-control-sm"
                                           value="{{ old('latitude') }}" placeholder="e.g. 27.4716">
                                    <span class="input-group-text text-muted fs-12">Lng</span>
                                    <input type="text" name="longitude" id="createLng"
                                           class="form-control form-control-sm"
                                           value="{{ old('longitude') }}" placeholder="e.g. 89.6386">
                                    <button type="button" class="btn btn-outline-primary btn-go-location"
                                            data-map-id="createMap" data-lat-id="createLat" data-lng-id="createLng"
                                            title="Go to this location on map">
                                        <i class="ri-crosshair-2-line"></i> Go
                                    </button>
                                </div>
                                <small class="text-muted">Or click directly on the map above to place a pin.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Site Notes</label>
                                <textarea name="site_notes" class="form-control" rows="2"
                                          placeholder="Any notes about this site...">{{ old('site_notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="ri-add-line me-1"></i> Create Site</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('assets/libs/leaflet/leaflet.js') }}"></script>
    <script>
    const OSM_TILES   = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    const OSM_ATTR    = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
    const DEFAULT_LAT = 27.4716, DEFAULT_LNG = 89.6386, DEFAULT_ZOOM = 13;

    // Registry: mapId → { map, placePin }
    const mapRegistry = {};

    // ── Map initialisation ────────────────────────────────────────
    function initMap(mapId, latEl, lngEl, initLat, initLng) {
        const hasCoords = parseFloat(initLat) && parseFloat(initLng);
        const lat  = hasCoords ? parseFloat(initLat) : DEFAULT_LAT;
        const lng  = hasCoords ? parseFloat(initLng) : DEFAULT_LNG;
        const zoom = hasCoords ? DEFAULT_ZOOM : 6;

        const map = L.map(mapId).setView([lat, lng], zoom);
        L.tileLayer(OSM_TILES, { attribution: OSM_ATTR, maxZoom: 19 }).addTo(map);

        let marker = hasCoords ? L.marker([lat, lng]).addTo(map) : null;

        function placePin(la, lo) {
            if (marker) map.removeLayer(marker);
            marker = L.marker([la, lo]).addTo(map);
            map.setView([la, lo], DEFAULT_ZOOM);
            latEl.value = parseFloat(la).toFixed(7);
            lngEl.value = parseFloat(lo).toFixed(7);
        }

        map.on('click', function (e) { placePin(e.latlng.lat, e.latlng.lng); });

        [latEl, lngEl].forEach(function (el) {
            el.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter') return;
                e.preventDefault();
                const la = parseFloat(latEl.value);
                const lo = parseFloat(lngEl.value);
                if (!isNaN(la) && !isNaN(lo)) placePin(la, lo);
            });
        });

        mapRegistry[mapId] = { map, placePin };
        return mapRegistry[mapId];
    }

    // ── Debounce helper ───────────────────────────────────────────
    function debounce(fn, delay) {
        let timer;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(fn, delay);
        };
    }

    // ── Address autocomplete ──────────────────────────────────────
    function hideSuggestions(ulEl) {
        ulEl.innerHTML = '';
        ulEl.style.display = 'none';
    }

    function renderSuggestions(results, ulEl, textareaEl, mapId) {
        ulEl.innerHTML = '';
        if (!results.length) {
            const li = document.createElement('li');
            li.className = 'suggestion-searching text-muted';
            li.textContent = 'No results found.';
            ulEl.appendChild(li);
            ulEl.style.display = 'block';
            return;
        }
        results.forEach(function (r) {
            const li = document.createElement('li');
            li.className = 'suggestion-item';
            li.innerHTML = '<i class="ri-map-pin-line me-2 text-primary"></i>' + r.display_name;
            li.addEventListener('mousedown', function (e) {
                e.preventDefault();
                textareaEl.value = r.display_name;
                hideSuggestions(ulEl);
                if (mapRegistry[mapId]) {
                    mapRegistry[mapId].placePin(parseFloat(r.lat), parseFloat(r.lon));
                }
            });
            ulEl.appendChild(li);
        });
        ulEl.style.display = 'block';
    }

    function attachAutocomplete(textareaEl) {
        const ulEl  = document.getElementById(textareaEl.dataset.suggestions);
        const mapId = textareaEl.dataset.mapId;

        const doSearch = debounce(function () {
            const q = textareaEl.value.trim();
            if (q.length < 3) { hideSuggestions(ulEl); return; }

            ulEl.innerHTML = '<li class="suggestion-searching"><i class="ri-loader-4-line me-1"></i>Searching...</li>';
            ulEl.style.display = 'block';

            fetch('https://nominatim.openstreetmap.org/search?format=json&limit=5&q=' + encodeURIComponent(q), {
                headers: { 'Accept-Language': 'en' }
            })
            .then(function (r) { return r.json(); })
            .then(function (results) { renderSuggestions(results, ulEl, textareaEl, mapId); })
            .catch(function () { hideSuggestions(ulEl); });
        }, 500);

        textareaEl.addEventListener('input', doSearch);

        // Keyboard navigation inside the dropdown
        textareaEl.addEventListener('keydown', function (e) {
            const items  = ulEl.querySelectorAll('.suggestion-item');
            const active = ulEl.querySelector('.suggestion-item.active');
            if (!items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!active) { items[0].classList.add('active'); }
                else {
                    active.classList.remove('active');
                    (active.nextElementSibling || items[0]).classList.add('active');
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (active) {
                    active.classList.remove('active');
                    (active.previousElementSibling || items[items.length - 1]).classList.add('active');
                }
            } else if (e.key === 'Enter') {
                const sel = ulEl.querySelector('.suggestion-item.active');
                if (sel) { e.preventDefault(); sel.dispatchEvent(new MouseEvent('mousedown')); }
            } else if (e.key === 'Escape') {
                hideSuggestions(ulEl);
            }
        });

        textareaEl.addEventListener('blur', function () {
            setTimeout(function () { hideSuggestions(ulEl); }, 150);
        });
    }

    // Attach autocomplete to all address textareas
    document.querySelectorAll('textarea[data-suggestions]').forEach(attachAutocomplete);

    // ── Create modal ──────────────────────────────────────────────
    document.getElementById('createSiteModal').addEventListener('shown.bs.modal', function () {
        if (mapRegistry['createMap']) { mapRegistry['createMap'].map.invalidateSize(); return; }
        const latEl = document.getElementById('createLat');
        const lngEl = document.getElementById('createLng');
        initMap('createMap', latEl, lngEl, latEl.value, lngEl.value);
    });

    // ── Edit modals ───────────────────────────────────────────────
    document.querySelectorAll('[id^="editSiteModal"]').forEach(function (modal) {
        modal.addEventListener('shown.bs.modal', function () {
            const siteId = modal.id.replace('editSiteModal', '');
            const mapId  = 'editMap' + siteId;
            if (mapRegistry[mapId]) { mapRegistry[mapId].map.invalidateSize(); return; }
            const latEl = document.getElementById('editLat' + siteId);
            const lngEl = document.getElementById('editLng' + siteId);
            initMap(mapId, latEl, lngEl, latEl.value, lngEl.value);
        });
    });

    // ── "Go to Location" buttons ──────────────────────────────────
    document.querySelectorAll('.btn-go-location').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const mapId = btn.dataset.mapId;
            const la    = parseFloat(document.getElementById(btn.dataset.latId).value);
            const lo    = parseFloat(document.getElementById(btn.dataset.lngId).value);
            if (isNaN(la) || isNaN(lo)) { alert('Please enter valid latitude and longitude values.'); return; }
            if (!mapRegistry[mapId]) { alert('Please open the map first.'); return; }
            mapRegistry[mapId].placePin(la, lo);
        });
    });

    // ── Re-open create modal on validation error ──────────────────
    @if($errors->has('client_id') || $errors->has('address') || $errors->has('latitude') || $errors->has('longitude'))
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('createSiteModal')).show();
    });
    @endif
    </script>
    @endpush

</x-app-layout>
