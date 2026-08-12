<x-app-layout>
    <x-slot name="title">Add Asset</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Add Asset</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.assets.index') }}">Asset Register</a></li>
                        <li class="breadcrumb-item active">Add Asset</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.assets.store') }}">
        @csrf
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
                                        data-client="{{ $site->client->name ?? '' }}"
                                        {{ old('site_id') == $site->id ? 'selected' : '' }}>
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
                                </select>
                                @error('building_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Zone / Area</label>
                                <input type="text" name="zone"
                                       class="form-control @error('zone') is-invalid @enderror"
                                       value="{{ old('zone') }}" placeholder="e.g. Rooftop North">
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
                                       value="{{ old('asset_code') }}" placeholder="e.g. AP01" required>
                                <div class="form-text">Must be unique within the selected site.</div>
                                @error('asset_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Asset Type <span class="text-danger">*</span></label>
                                <select name="asset_type"
                                        class="form-select @error('asset_type') is-invalid @enderror" required>
                                    <option value="">— Select Type —</option>
                                    <option value="anchor_point"  {{ old('asset_type') == 'anchor_point'  ? 'selected' : '' }}>Anchor Point</option>
                                    <option value="static_line"   {{ old('asset_type') == 'static_line'   ? 'selected' : '' }}>Static Line</option>
                                    <option value="ladder"        {{ old('asset_type') == 'ladder'        ? 'selected' : '' }}>Ladder</option>
                                    <option value="guardrail"     {{ old('asset_type') == 'guardrail'     ? 'selected' : '' }}>Guardrail</option>
                                    <option value="walkway"       {{ old('asset_type') == 'walkway'       ? 'selected' : '' }}>Walkway</option>
                                    <option value="other"         {{ old('asset_type') == 'other'         ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('asset_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Group ID
                                    <span class="text-muted fs-11" title="Links assets created from the same grouped entry (e.g. AP01–AP06)">
                                        <i class="ri-information-line"></i>
                                    </span>
                                </label>
                                <input type="text" name="group_id"
                                       class="form-control @error('group_id') is-invalid @enderror"
                                       value="{{ old('group_id') }}" placeholder="UUID (optional)">
                                @error('group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                       value="{{ old('make') }}" placeholder="Manufacturer">
                                @error('make')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Model</label>
                                <input type="text" name="model"
                                       class="form-control @error('model') is-invalid @enderror"
                                       value="{{ old('model') }}" placeholder="Model number">
                                @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Serial / Batch No.</label>
                                <input type="text" name="serial_or_batch"
                                       class="form-control @error('serial_or_batch') is-invalid @enderror"
                                       value="{{ old('serial_or_batch') }}" placeholder="Serial or batch">
                                @error('serial_or_batch')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Rating</label>
                                <input type="text" name="rating"
                                       class="form-control @error('rating') is-invalid @enderror"
                                       value="{{ old('rating') }}" placeholder="e.g. 12kN">
                                @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fixing Type</label>
                                <input type="text" name="fixing_type"
                                       class="form-control @error('fixing_type') is-invalid @enderror"
                                       value="{{ old('fixing_type') }}" placeholder="e.g. Through-bolt">
                                @error('fixing_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right column --}}
            <div class="col-lg-4">

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
                                   value="{{ old('install_date') }}">
                            @error('install_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">Next Inspection Due</label>
                            <input type="date" name="next_inspection_due_date"
                                   class="form-control @error('next_inspection_due_date') is-invalid @enderror"
                                   value="{{ old('next_inspection_due_date') }}">
                            @error('next_inspection_due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Replacement --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-arrow-left-right-line me-2 text-primary"></i>Replacement</h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label">Replaces Asset</label>
                        <select name="replaces_asset_id"
                                class="form-select form-select-sm @error('replaces_asset_id') is-invalid @enderror">
                            <option value="">— None —</option>
                            @foreach($assets as $a)
                            <option value="{{ $a->id }}" {{ old('replaces_asset_id') == $a->id ? 'selected' : '' }}>
                                {{ $a->asset_code }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-text">Select only if this asset is replacing an existing one.</div>
                        @error('replaces_asset_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="ri-save-line me-1"></i> Create Asset
                    </button>
                    <a href="{{ route('admin.assets.index') }}" class="btn btn-light">Cancel</a>
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

    const oldBuildingId = '{{ old('building_id') }}';

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

    // Pre-populate on validation error re-render
    const initSite = document.getElementById('siteSelect').value;
    if (initSite) loadBuildings(initSite, oldBuildingId);
    </script>
    @endpush

</x-app-layout>
