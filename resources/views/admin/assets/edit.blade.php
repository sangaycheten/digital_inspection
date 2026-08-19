<x-app-layout>
    <x-slot name="title">Edit Asset — {{ $asset->asset_code }}</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Edit Asset — <span class="text-primary">{{ $asset->asset_code }}</span></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.assets.index') }}">Asset Register</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.assets.show', $asset) }}">{{ $asset->asset_code }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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

    <form method="POST" action="{{ route('admin.assets.update', $asset) }}">
        @csrf @method('PUT')
        <div class="row g-3">

            {{-- Left column --}}
            <div class="col-lg-8">

                {{-- Location --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-map-pin-line me-2 text-primary"></i>Location</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Site <span class="text-danger">*</span></label>
                                <select name="site_id" id="siteSelect"
                                        class="form-select @error('site_id') is-invalid @enderror" required>
                                    <option value="">— Select Site —</option>
                                    @foreach($sites as $site)
                                    <option value="{{ $site->id }}"
                                        {{ (old('site_id', $asset->site_id) == $site->id) ? 'selected' : '' }}>
                                        {{ $site->name ?? $site->address }}
                                        @if($site->client) ({{ $site->client->name }}) @endif
                                    </option>
                                    @endforeach
                                </select>
                                @error('site_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Building</label>
                                <select name="building_id" id="buildingSelect"
                                        class="form-select @error('building_id') is-invalid @enderror">
                                    <option value="">— Select Building —</option>
                                    @foreach($buildings as $building)
                                    <option value="{{ $building->id }}"
                                        {{ (old('building_id', $asset->building_id) == $building->id) ? 'selected' : '' }}>
                                        {{ $building->name_or_level }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('building_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Zone / Area</label>
                                <input type="text" name="zone"
                                       class="form-control @error('zone') is-invalid @enderror"
                                       value="{{ old('zone', $asset->zone) }}" placeholder="e.g. Rooftop North">
                                @error('zone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Asset Identity --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-barcode-line me-2 text-primary"></i>Asset Identity</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Asset Code <span class="text-danger">*</span></label>
                                <input type="text" name="asset_code"
                                       class="form-control @error('asset_code') is-invalid @enderror"
                                       value="{{ old('asset_code', $asset->asset_code) }}" required>
                                @error('asset_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Asset Type <span class="text-danger">*</span></label>
                                <select name="asset_type"
                                        class="form-select @error('asset_type') is-invalid @enderror" required>
                                    <option value="">— Select Type —</option>
                                    @foreach($assetTypes as $val => $label)
                                    <option value="{{ $val }}" {{ old('asset_type', $asset->asset_type) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('asset_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Equipment Details --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-settings-2-line me-2 text-primary"></i>Equipment Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Make</label>
                                <input type="text" name="make"
                                       class="form-control @error('make') is-invalid @enderror"
                                       value="{{ old('make', $asset->make) }}" placeholder="Manufacturer">
                                @error('make')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Model</label>
                                <input type="text" name="model"
                                       class="form-control @error('model') is-invalid @enderror"
                                       value="{{ old('model', $asset->model) }}" placeholder="Model number">
                                @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Serial / Batch No.</label>
                                <input type="text" name="serial_or_batch"
                                       class="form-control @error('serial_or_batch') is-invalid @enderror"
                                       value="{{ old('serial_or_batch', $asset->serial_or_batch) }}">
                                @error('serial_or_batch')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Rating</label>
                                <input type="text" name="rating"
                                       class="form-control @error('rating') is-invalid @enderror"
                                       value="{{ old('rating', $asset->rating) }}" placeholder="e.g. 12kN">
                                @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fixing Type</label>
                                <input type="text" name="fixing_type"
                                       class="form-control @error('fixing_type') is-invalid @enderror"
                                       value="{{ old('fixing_type', $asset->fixing_type) }}" placeholder="e.g. Through-bolt">
                                @error('fixing_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right column --}}
            <div class="col-lg-4">

                {{-- Current Status (read-only info) --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-shield-check-line me-2 text-primary"></i>Current Status</h6>
                    </div>
                    <div class="card-body">
                        @php
                        $statusColors = [
                            'pass'           => 'success',
                            'fail'           => 'danger',
                            'under_review'   => 'warning',
                            'restricted_use' => 'orange',
                            'not_inspected'  => 'secondary',
                            'not_located'    => 'dark',
                            'removed'        => 'dark',
                            'replaced'       => 'info',
                        ];
                        $sc = $statusColors[$asset->current_status] ?? 'secondary';
                        @endphp
                        <div class="d-flex align-items-center gap-2 mb-2">
                            @if($sc === 'orange')
                            <span class="badge fs-13 px-3 py-2" style="background-color:#fd7e14;color:#fff;">
                                {{ ucwords(str_replace('_', ' ', $asset->current_status)) }}
                            </span>
                            @else
                            <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} fs-13 px-3 py-2">
                                {{ ucwords(str_replace('_', ' ', $asset->current_status)) }}
                            </span>
                            @endif
                        </div>
                        <p class="text-muted fs-12 mb-0">Status is updated automatically when an inspection is approved.</p>

                        @if(!in_array($asset->current_status, ['removed', 'replaced']))
                        <hr class="my-3">
                        <p class="text-muted fs-12 mb-2">Mark this asset as removed if it has been physically taken out of service.</p>
                        <form method="POST" action="{{ route('admin.assets.remove', $asset) }}"
                              onsubmit="return confirm('Mark this asset as removed? This cannot be undone via the UI.')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                <i class="ri-delete-bin-line me-1"></i> Mark as Removed
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                {{-- Dates --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-calendar-line me-2 text-primary"></i>Dates</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Install Date</label>
                            <input type="date" name="install_date"
                                   class="form-control @error('install_date') is-invalid @enderror"
                                   value="{{ old('install_date', $asset->install_date?->format('Y-m-d')) }}">
                            @error('install_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">Next Inspection Due</label>
                            <input type="date" name="next_inspection_due_date"
                                   class="form-control @error('next_inspection_due_date') is-invalid @enderror"
                                   value="{{ old('next_inspection_due_date', $asset->next_inspection_due_date?->format('Y-m-d')) }}">
                            @error('next_inspection_due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="ri-save-line me-1"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.assets.show', $asset) }}" class="btn btn-light">Cancel</a>
                </div>

            </div>
        </div>
    </form>

    @push('scripts')
    <script>
    const buildingsBySite = @json(
        \App\Models\Building::all(['id', 'site_id', 'name_or_level'])
            ->groupBy('site_id')
            ->map(fn ($b) => $b->values())
    );

    const currentBuildingId = '{{ old('building_id', $asset->building_id) }}';

    function loadBuildings(siteId, selectedId) {
        const sel = document.getElementById('buildingSelect');
        sel.innerHTML = '<option value="">— Select Building —</option>';
        const buildings = buildingsBySite[siteId] || [];
        buildings.forEach(function (b) {
            const opt = document.createElement('option');
            opt.value = b.id;
            opt.textContent = b.name_or_level;
            if (b.id === selectedId) opt.selected = true;
            sel.appendChild(opt);
        });
    }

    document.getElementById('siteSelect').addEventListener('change', function () {
        loadBuildings(this.value, null);
    });

    // Pre-populate current values
    const initSite = document.getElementById('siteSelect').value;
    if (initSite) loadBuildings(initSite, currentBuildingId);
    </script>
    @endpush

</x-app-layout>
