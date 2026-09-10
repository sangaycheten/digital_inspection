<x-app-layout>
    <x-slot name="title">Buildings</x-slot>

    @push('styles')
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
                    @can('add buildings')
                    @if($sites->isNotEmpty())
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createBuildingModal">
                        <i class="ri-add-line me-1"></i> Add Building
                    </button>
                    @endif
                    @endcan
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
                                                        <span class="badge" style="<?= 'background:'.e($zone['color'] ?? '#6c757d').';color:#fff' ?>">
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
                                            @can('edit buildings')
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#editBuildingModal{{ $building->id }}">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            @endcan
                                            @can('delete buildings')
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal" data-bs-target="#deleteBuildingModal{{ $building->id }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan
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
                                                                                @if($building->site->client_id != $site->client_id) style="display:none" @endif>
                                                                            {{ $site->name ?: Str::limit($site->address, 40) }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Building Code <span class="text-danger">*</span></label>
                                                                    <input type="text" name="building_code" class="form-control"
                                                                           value="{{ $building->building_code }}" required maxlength="50">
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <label class="form-label">Building / Level Name <span class="text-danger">*</span></label>
                                                                    <input type="text" name="name_or_level" class="form-control"
                                                                           value="{{ $building->name_or_level }}" required>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label d-flex align-items-center gap-2">
                                                                        <i class="ri-map-2-line text-primary"></i>Roof Zones
                                                                        <small class="text-muted fw-normal">Pick colour → name zone → Draw on map</small>
                                                                    </label>
                                                                    <div id="editZoneWrap{{ $building->id }}">
                                                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                                                        @foreach(['#ef4444','#f97316','#eab308','#22c55e','#3b82f6','#a855f7'] as $c)
                                                                        <span class="color-swatch {{ $loop->first ? 'active' : '' }}"
                                                                              style="<?= e($c) ? 'background:'.e($c) : '' ?>"
                                                                              data-color="{{ $c }}"
                                                                              onclick="setZoneColor('edit{{ $building->id }}', this)"></span>
                                                                        @endforeach
                                                                        <input type="text" id="editZoneName{{ $building->id }}"
                                                                               class="form-control form-control-sm" style="max-width:150px"
                                                                               placeholder="Zone name...">
                                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                                                data-role="draw-zone"
                                                                                onclick="startDraw('edit{{ $building->id }}')">
                                                                            <i class="ri-edit-2-line me-1"></i>Draw Zone
                                                                        </button>
                                                                        <button type="button" class="btn btn-sm btn-success d-none"
                                                                                data-role="finish-zone"
                                                                                onclick="finishDraw('edit{{ $building->id }}')">
                                                                            <i class="ri-check-line me-1"></i>Finish Zone
                                                                        </button>
                                                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                                onclick="clearAllZones('edit{{ $building->id }}')">
                                                                            <i class="ri-delete-bin-line me-1"></i>Clear All
                                                                        </button>
                                                                    </div>
                                                                    <div class="alert alert-info fs-12 py-2 mb-2 d-none" data-role="draw-hint">
                                                                        <i class="ri-information-line me-1"></i>Click on the map to add points. Double-click or press <strong>Finish Zone</strong> to close the shape. <kbd>Esc</kbd> cancels.
                                                                    </div>
                                                                    <div id="editZoneMap{{ $building->id }}" class="zone-map mb-2"></div>
                                                                    <div id="editZoneList{{ $building->id }}" class="d-flex flex-wrap gap-1 mb-2"></div>
                                                                    <input type="hidden" name="roof_zones" id="editRoofZones{{ $building->id }}">
                                                                    </div>
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
                            <div class="col-md-4">
                                <label class="form-label">Building Code <span class="text-danger">*</span></label>
                                <input type="text" name="building_code" class="form-control @error('building_code') is-invalid @enderror"
                                       value="{{ old('building_code') }}" required maxlength="50">
                                @error('building_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Building / Level Name <span class="text-danger">*</span></label>
                                <input type="text" name="name_or_level" class="form-control @error('name_or_level') is-invalid @enderror"
                                       value="{{ old('name_or_level') }}" required placeholder="e.g. Level 1, Roof Top">
                                @error('name_or_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12" id="createZoneWrap">
                                <label class="form-label d-flex align-items-center gap-2">
                                    <i class="ri-map-2-line text-primary"></i>Roof Zones
                                    <small class="text-muted fw-normal">Pick colour → name zone → Draw on map</small>
                                </label>
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                    @foreach(['#ef4444','#f97316','#eab308','#22c55e','#3b82f6','#a855f7'] as $c)
                                    <span class="color-swatch {{ $loop->first ? 'active' : '' }}"
                                          style="<?= e($c) ? 'background:'.e($c) : '' ?>"
                                          data-color="{{ $c }}"
                                          onclick="setZoneColor('create', this)"></span>
                                    @endforeach
                                    <input type="text" id="createZoneName"
                                           class="form-control form-control-sm" style="max-width:150px"
                                           placeholder="Zone name...">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-role="draw-zone"
                                            onclick="startDraw('create')">
                                        <i class="ri-edit-2-line me-1"></i>Draw Zone
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success d-none"
                                            data-role="finish-zone"
                                            onclick="finishDraw('create')">
                                        <i class="ri-check-line me-1"></i>Finish Zone
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="clearAllZones('create')">
                                        <i class="ri-delete-bin-line me-1"></i>Clear All
                                    </button>
                                </div>
                                <div class="alert alert-light border fs-12 py-2 mb-2" id="createMapHint">
                                    <i class="ri-information-line me-1"></i>Select a site above to load the map at the site location.
                                </div>
                                <div class="alert alert-info fs-12 py-2 mb-2 d-none" data-role="draw-hint">
                                    <i class="ri-information-line me-1"></i>Click on the map to add points. Double-click or press <strong>Finish Zone</strong> to close the shape. <kbd>Esc</kbd> cancels.
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
    @include('partials.google-maps')
    <script src="{{ asset('assets/js/maps/gmap-zone-editor.js') }}"></script>
    <script>
    // Site coordinates lookup, consumed by centerMap() in gmap-zone-editor.js
    window.siteCoords = {!! json_encode($sites->mapWithKeys(fn ($s) => [$s->id => ['lat' => $s->latitude ? (float)$s->latitude : null, 'lng' => $s->longitude ? (float)$s->longitude : null]])) !!};

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

    // ── Create modal: site change ────────────────────────────────────
    function onCreateSiteChange(siteId) {
        const mapEl = document.getElementById('createZoneMap');
        const hint  = document.getElementById('createMapHint');
        if (!siteId) return;

        const c = window.siteCoords[siteId];
        mapEl.style.display = 'block';
        hint.style.display  = 'none';

        const ed = window.getZoneEditor('create');
        if (!ed.ready) {
            initZoneMap('create', 'createZoneMap', c ? c.lat : null, c ? c.lng : null);
        } else {
            centerMap('create', siteId);
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

    // ── Edit modal open → init map + load saved zones once ───────────
    document.querySelectorAll('[id^="editBuildingModal"]').forEach(function (modal) {
        modal.addEventListener('shown.bs.modal', function () {
            const buildingId = modal.id.replace('editBuildingModal', '');
            const key        = 'edit' + buildingId;
            const siteId     = modal.dataset.siteId;
            const c          = window.siteCoords[siteId] || {};

            initZoneMap(key, 'editZoneMap' + buildingId, c.lat, c.lng);

            const ed = window.getZoneEditor(key);
            if (!ed._loaded) {
                ed._loaded = true;
                try {
                    loadExistingZones(key, JSON.parse(modal.dataset.zones || '[]'));
                } catch (e) {}
            }
        });
    });

    // ── Re-open create modal on validation error ──────────────────────
    @if($errors->has('site_id') || $errors->has('name_or_level') || $errors->has('building_code'))
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('createBuildingModal')).show();
    });
    @endif
    </script>
    @endpush

</x-app-layout>
