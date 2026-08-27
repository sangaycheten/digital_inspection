<x-app-layout>
    <x-slot name="title">Buildings</x-slot>

    @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/leaflet/leaflet.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/leaflet/leaflet.draw.css') }}">
    <style>
        .zone-map { height: 260px; width: 100%; border-radius: 6px; border: 1px solid #dee2e6; }
        .color-swatch {
            width: 26px; height: 26px; border-radius: 50%; border: 3px solid transparent;
            cursor: pointer; transition: transform .15s, border-color .15s;
        }
        .color-swatch:hover { transform: scale(1.15); }
        .color-swatch.active { border-color: #000; transform: scale(1.15); }
        .zone-badge {
            display:inline-flex; align-items:center; gap:4px;
            padding: 4px 10px; border-radius:999px; color:#fff; font-size:12px; font-weight:500;
        }
        .zone-badge .ri-close-line { cursor:pointer; opacity:.7; }
        .zone-badge .ri-close-line:hover { opacity:1; }
    </style>
    @endpush

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Buildings</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item active">Buildings</li>
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
                        <i class="ri-home-office-line me-2 text-primary"></i>All Buildings
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $buildings->total() }}</span>
                    </h5>
                    @if($sites->isNotEmpty())
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createBuildingModal">
                        <i class="ri-add-line me-1"></i> Add Building
                    </button>
                    @endif
                </div>

                <div class="card-body border-bottom pb-3">
                    <form method="GET" action="{{ route('admin.master.buildings.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label text-muted fs-12 mb-1">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search by name or level..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted fs-12 mb-1">Site</label>
                            <select name="site_id" class="form-select form-select-sm">
                                <option value="">All Sites</option>
                                @foreach($sites as $site)
                                <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                    {{ $site->client->name ?? '' }} — {{ $site->name ?: Str::limit($site->address, 40) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i> Filter</button>
                            <a href="{{ route('admin.master.buildings.index') }}" class="btn btn-light btn-sm ms-1"><i class="ri-refresh-line"></i> Reset</a>
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
                                    <th>Site</th>
                                    <th>Building / Level</th>
                                    <th>Roof Zones</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($buildings as $building)
                                <tr>
                                    <td class="ps-3 text-muted fs-12">{{ $buildings->firstItem() + $loop->index }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary fs-11">
                                            {{ $building->site->client->custom_client_code ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="text-muted fs-12">{{ $building->site->name ?: Str::limit($building->site->address ?? '—', 35) }}</td>
                                    <td class="fw-medium">{{ $building->name_or_level }}</td>
                                    <td>
                                        @if($building->roof_zones)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($building->roof_zones as $zone)
                                                    @if(is_array($zone))
                                                        <span class="badge" style="background:{{ $zone['color'] ?? '#6c757d' }}; color:#fff">
                                                            {{ $zone['name'] ?? '?' }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-dark">{{ $zone }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted fs-12">{{ $building->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="hstack gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#editBuildingModal{{ $building->id }}">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal" data-bs-target="#deleteBuildingModal{{ $building->id }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>

                                        {{-- Edit Modal --}}
                                        <div class="modal fade" id="editBuildingModal{{ $building->id }}"
                                             tabindex="-1" aria-hidden="true"
                                             data-zones="{{ json_encode($building->roof_zones ?? []) }}"
                                             data-site-id="{{ $building->site_id }}">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Building</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="{{ route('admin.master.buildings.update', $building) }}">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Client <span class="text-danger">*</span></label>
                                                                    <select id="editClientSelect{{ $building->id }}" class="form-select"
                                                                            onchange="filterSites('editClientSelect{{ $building->id }}', 'editSiteSelect{{ $building->id }}')">
                                                                        <option value="">-- Select Client --</option>
                                                                        @foreach($clients as $client)
                                                                        <option value="{{ $client->id }}"
                                                                            {{ $building->site->client_id == $client->id ? 'selected' : '' }}>
                                                                            {{ $client->name }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Site <span class="text-danger">*</span></label>
                                                                    <select name="site_id" id="editSiteSelect{{ $building->id }}" class="form-select" required
                                                                            onchange="onEditSiteChange('{{ $building->id }}', this.value)">
                                                                        @foreach($sites as $site)
                                                                        <option value="{{ $site->id }}"
                                                                                data-client="{{ $site->client_id }}"
                                                                                {{ $building->site_id == $site->id ? 'selected' : '' }}
                                                                                style="{{ $building->site->client_id == $site->client_id ? '' : 'display:none' }}">
                                                                            {{ $site->name ?: Str::limit($site->address, 40) }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label">Building / Level Name <span class="text-danger">*</span></label>
                                                                    <input type="text" name="name_or_level" class="form-control"
                                                                           value="{{ $building->name_or_level }}" required>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label d-flex align-items-center gap-2">
                                                                        <i class="ri-map-2-line text-primary"></i>Roof Zones
                                                                        <small class="text-muted fw-normal">Pick colour → name zone → Draw on map</small>
                                                                    </label>
                                                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                                                        @foreach(['#ef4444','#f97316','#eab308','#22c55e','#3b82f6','#a855f7'] as $c)
                                                                        <span class="color-swatch {{ $loop->first ? 'active' : '' }}"
                                                                              style="background:{{ $c }}"
                                                                              data-color="{{ $c }}"
                                                                              onclick="setZoneColor('edit{{ $building->id }}', this)"></span>
                                                                        @endforeach
                                                                        <input type="text" id="editZoneName{{ $building->id }}"
                                                                               class="form-control form-control-sm" style="max-width:150px"
                                                                               placeholder="Zone name...">
                                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                                                onclick="startDraw('edit{{ $building->id }}')">
                                                                            <i class="ri-edit-2-line me-1"></i>Draw Zone
                                                                        </button>
                                                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                                onclick="clearAllZones('edit{{ $building->id }}')">
                                                                            <i class="ri-delete-bin-line me-1"></i>Clear All
                                                                        </button>
                                                                    </div>
                                                                    <div id="editZoneMap{{ $building->id }}" class="zone-map mb-2"></div>
                                                                    <div id="editZoneList{{ $building->id }}" class="d-flex flex-wrap gap-1 mb-2"></div>
                                                                    <input type="hidden" name="roof_zones" id="editRoofZones{{ $building->id }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary"
                                                                    onclick="serializeZones('edit{{ $building->id }}', 'editRoofZones{{ $building->id }}')">
                                                                <i class="ri-save-line me-1"></i> Save
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Delete Modal --}}
                                        <div class="modal fade" id="deleteBuildingModal{{ $building->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="avatar-sm mx-auto mb-3">
                                                            <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-22">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </span>
                                                        </div>
                                                        <h5 class="mb-3">Delete Building</h5>
                                                        <p class="text-muted mb-4">Are you sure you want to delete <strong>{{ $building->name_or_level }}</strong>?</p>
                                                        <div class="hstack gap-2 justify-content-center">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST" action="{{ route('admin.master.buildings.destroy', $building) }}">
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
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="ri-home-office-line fs-24 d-block mb-2"></i>No buildings found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($buildings->hasPages())
                <div class="card-footer">{{ $buildings->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createBuildingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-home-office-line me-2"></i>Add New Building</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.master.buildings.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Client <span class="text-danger">*</span></label>
                                <select id="createClientSelect" class="form-select" required
                                        onchange="filterSites('createClientSelect','createSiteSelect'); onCreateClientChange()">
                                    <option value="">-- Select Client --</option>
                                    @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Site <span class="text-danger">*</span></label>
                                <select name="site_id" id="createSiteSelect" class="form-select @error('site_id') is-invalid @enderror" required
                                        onchange="onCreateSiteChange(this.value)">
                                    <option value="">-- Select Site --</option>
                                    @foreach($sites as $site)
                                    <option value="{{ $site->id }}"
                                            data-client="{{ $site->client_id }}"
                                            {{ old('site_id') == $site->id ? 'selected' : '' }}
                                            style="display:none">
                                        {{ $site->name ?: Str::limit($site->address, 40) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('site_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Building / Level Name <span class="text-danger">*</span></label>
                                <input type="text" name="name_or_level" class="form-control @error('name_or_level') is-invalid @enderror"
                                       value="{{ old('name_or_level') }}" required placeholder="e.g. Level 1, Roof Top">
                                @error('name_or_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label d-flex align-items-center gap-2">
                                    <i class="ri-map-2-line text-primary"></i>Roof Zones
                                    <small class="text-muted fw-normal">Pick colour → name zone → Draw on map</small>
                                </label>
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                    @foreach(['#ef4444','#f97316','#eab308','#22c55e','#3b82f6','#a855f7'] as $c)
                                    <span class="color-swatch {{ $loop->first ? 'active' : '' }}"
                                          style="background:{{ $c }}"
                                          data-color="{{ $c }}"
                                          onclick="setZoneColor('create', this)"></span>
                                    @endforeach
                                    <input type="text" id="createZoneName"
                                           class="form-control form-control-sm" style="max-width:150px"
                                           placeholder="Zone name...">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="startDraw('create')">
                                        <i class="ri-edit-2-line me-1"></i>Draw Zone
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="clearAllZones('create')">
                                        <i class="ri-delete-bin-line me-1"></i>Clear All
                                    </button>
                                </div>
                                <div class="alert alert-light border fs-12 py-2 mb-2" id="createMapHint">
                                    <i class="ri-information-line me-1"></i>Select a site above to load the map at the site location.
                                </div>
                                <div id="createZoneMap" class="zone-map mb-2" style="display:none"></div>
                                <div id="createZoneList" class="d-flex flex-wrap gap-1 mb-2"></div>
                                <input type="hidden" name="roof_zones" id="createRoofZones">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"
                                onclick="serializeZones('create', 'createRoofZones')">
                            <i class="ri-add-line me-1"></i> Create Building
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('assets/libs/leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('assets/libs/leaflet/leaflet.draw.js') }}"></script>
    <script>
    const OSM_TILES   = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    const OSM_ATTR    = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';
    const DEFAULT_LAT = 27.4716, DEFAULT_LNG = 89.6386, DEFAULT_ZOOM = 19;

    // Site coordinates lookup
    const siteCoords = {
        @foreach($sites as $site)
        '{{ $site->id }}': { lat: {{ $site->latitude ?? 'null' }}, lng: {{ $site->longitude ?? 'null' }} },
        @endforeach
    };

    // ── Zone editor state per context key (create | editBUILDINGID) ──
    const zoneEditors = {};

    function getEditor(key) {
        if (!zoneEditors[key]) {
            zoneEditors[key] = { map: null, drawnItems: null, drawControl: null, zones: [], activeColor: '#ef4444' };
        }
        return zoneEditors[key];
    }

    // ── Client → Site filter ─────────────────────────────────────────
    function filterSites(clientSelectId, siteSelectId) {
        const clientId = document.getElementById(clientSelectId).value;
        const siteSelect = document.getElementById(siteSelectId);
        siteSelect.querySelectorAll('option[data-client]').forEach(opt => {
            const match = !clientId || opt.dataset.client === clientId;
            opt.style.display = match ? '' : 'none';
            if (!match && opt.selected) siteSelect.value = '';
        });
    }

    // ── Map initialisation ───────────────────────────────────────────
    function initZoneMap(key, mapElId, lat, lng) {
        const ed = getEditor(key);
        if (ed.map) { ed.map.invalidateSize(); return; }

        const hasCoords = lat && lng;
        const map = L.map(mapElId).setView(
            [hasCoords ? lat : DEFAULT_LAT, hasCoords ? lng : DEFAULT_LNG],
            hasCoords ? DEFAULT_ZOOM : 13
        );
        L.tileLayer(OSM_TILES, { attribution: OSM_ATTR, maxZoom: 22 }).addTo(map);

        const drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        const drawControl = new L.Control.Draw({
            draw: {
                polygon:   { shapeOptions: { color: ed.activeColor, fillColor: ed.activeColor, fillOpacity: 0.25 } },
                polyline:  false,
                rectangle: false,
                circle:    false,
                circlemarker: false,
                marker:    false,
            },
            edit: { featureGroup: drawnItems, remove: false }
        });
        map.addControl(drawControl);

        map.on(L.Draw.Event.CREATED, function (e) {
            const layer = e.layer;
            layer.setStyle({ color: ed.activeColor, fillColor: ed.activeColor, fillOpacity: 0.25 });
            drawnItems.addLayer(layer);

            const nameInput = document.getElementById(key === 'create' ? 'createZoneName' : key.replace('create','') + 'ZoneName' + key.replace('edit',''));
            // Resolve zone name input id
            const nameEl = getZoneNameEl(key);
            const zoneName = nameEl ? nameEl.value.trim() : '';

            const latlngs = layer.getLatLngs()[0].map(p => [p.lat, p.lng]);
            const zone = { name: zoneName || ('Zone ' + (ed.zones.length + 1)), color: ed.activeColor, polygon: latlngs, layer };
            ed.zones.push(zone);
            if (nameEl) nameEl.value = '';
            renderZoneList(key);
        });

        ed.map = map;
        ed.drawnItems = drawnItems;
        ed.drawControl = drawControl;
    }

    function getZoneNameEl(key) {
        if (key === 'create') return document.getElementById('createZoneName');
        const buildingId = key.replace('edit', '');
        return document.getElementById('editZoneName' + buildingId);
    }

    function centerMap(key, siteId) {
        const ed = getEditor(key);
        if (!ed.map) return;
        const c = siteCoords[siteId];
        if (c && c.lat && c.lng) {
            ed.map.setView([c.lat, c.lng], DEFAULT_ZOOM);
        }
    }

    // ── Color selection ──────────────────────────────────────────────
    function setZoneColor(key, el) {
        const ed = getEditor(key);
        ed.activeColor = el.dataset.color;
        el.closest('.d-flex').querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
        el.classList.add('active');
    }

    // ── Draw zone button ─────────────────────────────────────────────
    function startDraw(key) {
        const ed = getEditor(key);
        if (!ed.map) {
            alert('Please select a site first to load the map.');
            return;
        }
        // Update draw control color to match active selection
        ed.drawControl.setDrawingOptions({
            polygon: { shapeOptions: { color: ed.activeColor, fillColor: ed.activeColor, fillOpacity: 0.25 } }
        });
        new L.Draw.Polygon(ed.map, { shapeOptions: { color: ed.activeColor, fillColor: ed.activeColor, fillOpacity: 0.25 } }).enable();
    }

    // ── Render zone badges ───────────────────────────────────────────
    function renderZoneList(key) {
        const ed = getEditor(key);
        const listId = key === 'create' ? 'createZoneList' : 'editZoneList' + key.replace('edit', '');
        const listEl = document.getElementById(listId);
        if (!listEl) return;
        listEl.innerHTML = '';
        ed.zones.forEach((zone, i) => {
            const badge = document.createElement('span');
            badge.className = 'zone-badge';
            badge.style.background = zone.color;
            badge.innerHTML = `<i class="ri-map-2-line"></i>${zone.name}<i class="ri-close-line" onclick="removeZone('${key}', ${i})"></i>`;
            listEl.appendChild(badge);
        });
    }

    function removeZone(key, index) {
        const ed = getEditor(key);
        const zone = ed.zones[index];
        if (zone.layer && ed.drawnItems) ed.drawnItems.removeLayer(zone.layer);
        ed.zones.splice(index, 1);
        renderZoneList(key);
    }

    function clearAllZones(key) {
        const ed = getEditor(key);
        if (ed.drawnItems) ed.drawnItems.clearLayers();
        ed.zones = [];
        renderZoneList(key);
    }

    // ── Serialize zones to hidden input before submit ────────────────
    function serializeZones(key, inputId) {
        const ed = getEditor(key);
        const input = document.getElementById(inputId);
        if (!input) return;
        const data = ed.zones.map(z => ({ name: z.name, color: z.color, polygon: z.polygon }));
        input.value = data.length ? JSON.stringify(data) : '';
    }

    // ── Create modal: site change ────────────────────────────────────
    function onCreateSiteChange(siteId) {
        const mapEl = document.getElementById('createZoneMap');
        const hint  = document.getElementById('createMapHint');
        if (!siteId) return;

        const c = siteCoords[siteId];
        mapEl.style.display = 'block';
        hint.style.display  = 'none';

        if (!zoneEditors['create'] || !zoneEditors['create'].map) {
            initZoneMap('create', 'createZoneMap', c ? c.lat : null, c ? c.lng : null);
        } else {
            centerMap('create', siteId);
            zoneEditors['create'].map.invalidateSize();
        }
    }

    function onCreateClientChange() {
        // Reset site when client changes — map stays if already loaded
        document.getElementById('createSiteSelect').value = '';
    }

    // ── Edit modal: site change ──────────────────────────────────────
    function onEditSiteChange(buildingId, siteId) {
        centerMap('edit' + buildingId, siteId);
    }

    // ── Load existing zones into edit map ────────────────────────────
    function loadExistingZones(key, zones) {
        const ed = getEditor(key);
        if (!ed.map || !zones || !zones.length) return;

        zones.forEach(zone => {
            if (!zone.polygon || !zone.polygon.length) return;
            const latlngs = zone.polygon.map(p => Array.isArray(p) ? p : [p.lat, p.lng]);
            const layer = L.polygon(latlngs, {
                color: zone.color || '#3b82f6',
                fillColor: zone.color || '#3b82f6',
                fillOpacity: 0.25
            });
            ed.drawnItems.addLayer(layer);
            ed.zones.push({ name: zone.name, color: zone.color, polygon: zone.polygon, layer });
        });
        renderZoneList(key);
    }

    // ── Edit modal open → init map + load zones ───────────────────────
    document.querySelectorAll('[id^="editBuildingModal"]').forEach(function (modal) {
        modal.addEventListener('shown.bs.modal', function () {
            const buildingId = modal.id.replace('editBuildingModal', '');
            const key        = 'edit' + buildingId;
            const siteId     = modal.dataset.siteId;
            const c          = siteCoords[siteId] || {};

            initZoneMap(key, 'editZoneMap' + buildingId, c.lat, c.lng);

            // Load existing zones once
            const ed = getEditor(key);
            if (!ed._loaded) {
                ed._loaded = true;
                try {
                    const existing = JSON.parse(modal.dataset.zones || '[]');
                    loadExistingZones(key, existing);
                } catch(e) {}
            }
        });
    });

    // ── Re-open create modal on validation error ──────────────────────
    @if($errors->has('site_id') || $errors->has('name_or_level'))
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('createBuildingModal')).show();
    });
    @endif
    </script>
    @endpush

</x-app-layout>
