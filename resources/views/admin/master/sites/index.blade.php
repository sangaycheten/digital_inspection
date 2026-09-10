<x-app-layout>
    <x-slot name="title">Sites</x-slot>

    @push('styles')
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
                    @can('add sites')
                    @if($clients->isNotEmpty())
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createSiteModal">
                        <i class="ri-add-line me-1"></i> Add Site
                    </button>
                    @endif
                    @endcan
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
                                    <th>Created (Local Time)</th>
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
                                    <td class="text-muted fs-12">
                                        {{ site_time($site->created_at, $site->timezone) }}
                                    </td>
                                    <td>
                                        <div class="hstack gap-1">
                                            @can('edit sites')
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#editSiteModal{{ $site->id }}">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            @endcan
                                            @can('delete sites')
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal" data-bs-target="#deleteSiteModal{{ $site->id }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan
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
                                                                    <input type="text" name="name" class="form-control" value="{{ $site->name }}" placeholder="">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                                                    <div class="position-relative">
                                                                        <textarea name="address" class="form-control" rows="2" required
                                                                                  autocomplete="off"
                                                                                  data-suggestions="editSuggestions{{ $site->id }}"
                                                                                  data-map-id="editMap{{ $site->id }}"
                                                                                  data-region-codes="au"
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
                                                                <div class="col-12">
                                                                    <input type="hidden" name="timezone" id="editTimezone{{ $site->id }}" value="{{ $site->timezone ?? 'UTC' }}">
                                                                    <div class="d-flex align-items-center gap-2 fs-12 text-muted">
                                                                        <i class="ri-time-zone-line"></i> Timezone:
                                                                        <span id="editTimezoneDisplay{{ $site->id }}" class="badge bg-primary-subtle text-primary fs-12">
                                                                            {{ $site->timezone ?? 'UTC' }}
                                                                        </span>
                                                                        <span id="editTimezoneLoading{{ $site->id }}" class="text-muted fs-11" style="display:none;">
                                                                            <i class="ri-loader-4-line"></i> Detecting...
                                                                        </span>
                                                                    </div>
                                                                    <div class="form-text">Auto-detected from pin location.</div>
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
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <textarea name="address" id="createAddress"
                                              class="form-control @error('address') is-invalid @enderror"
                                              rows="2" required autocomplete="off"
                                              data-suggestions="createSuggestions"
                                              data-map-id="createMap"
                                              data-region-codes="au"
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
                            <div class="col-12">
                                <input type="hidden" name="timezone" id="createTimezone" value="{{ old('timezone', 'UTC') }}">
                                <div class="d-flex align-items-center gap-2 fs-12 text-muted">
                                    <i class="ri-time-zone-line"></i> Timezone:
                                    <span id="createTimezoneDisplay" class="badge bg-secondary-subtle text-secondary fs-12">
                                        {{ old('timezone', 'UTC') }}
                                    </span>
                                    <span id="createTimezoneLoading" class="text-muted fs-11" style="display:none;">
                                        <i class="ri-loader-4-line"></i> Detecting...
                                    </span>
                                </div>
                                <div class="form-text">Auto-detected when you place a pin on the map.</div>
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
    @include('partials.google-maps')
    <script src="{{ asset('assets/js/maps/gmap-picker.js') }}"></script>
    <script>
    // Address autocomplete on every address textarea (create + all edit modals).
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('textarea[data-suggestions]').forEach(function (el) {
            GMapPicker.attachAutocomplete(el);
        });
    });

    function recenter(key) {
        const slot = GMapPicker.get(key);
        if (!slot) return false;
        Promise.resolve(slot.ready).then(function (s) { if (s && s.setCenter) s.setCenter(); });
        return true;
    }

    // -- Create modal --
    document.getElementById('createSiteModal').addEventListener('shown.bs.modal', function () {
        if (recenter('createMap')) return;
        GMapPicker.init({
            mapId:       'createMap',
            latEl:       document.getElementById('createLat'),
            lngEl:       document.getElementById('createLng'),
            addressEl:   document.getElementById('createAddress'),
            tzInputEl:   document.getElementById('createTimezone'),
            tzDisplayEl: document.getElementById('createTimezoneDisplay'),
            tzLoadingEl: document.getElementById('createTimezoneLoading'),
        });
    });

    // -- Edit modals --
    document.querySelectorAll('[id^="editSiteModal"]').forEach(function (modal) {
        modal.addEventListener('shown.bs.modal', function () {
            const siteId = modal.id.replace('editSiteModal', '');
            const mapKey = 'editMap' + siteId;
            if (recenter(mapKey)) return;
            GMapPicker.init({
                mapId:       mapKey,
                latEl:       document.getElementById('editLat' + siteId),
                lngEl:       document.getElementById('editLng' + siteId),
                addressEl:   modal.querySelector('textarea[name="address"]'),
                tzInputEl:   document.getElementById('editTimezone' + siteId),
                tzDisplayEl: document.getElementById('editTimezoneDisplay' + siteId),
                tzLoadingEl: document.getElementById('editTimezoneLoading' + siteId),
            });
        });
    });

    // -- "Go to Location" buttons --
    document.querySelectorAll('.btn-go-location').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const la = parseFloat(document.getElementById(btn.dataset.latId).value);
            const lo = parseFloat(document.getElementById(btn.dataset.lngId).value);
            if (isNaN(la) || isNaN(lo)) { alert('Please enter valid latitude and longitude values.'); return; }
            if (!GMapPicker.get(btn.dataset.mapId)) { alert('Please open the map first.'); return; }
            GMapPicker.placePinOn(btn.dataset.mapId, la, lo);
        });
    });

    // -- Re-open create modal on validation error --
    @if($errors->has('client_id') || $errors->has('address') || $errors->has('latitude') || $errors->has('longitude'))
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('createSiteModal')).show();
    });
    @endif
    </script>
    @endpush

</x-app-layout>
