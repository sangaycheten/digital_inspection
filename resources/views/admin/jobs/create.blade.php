<x-app-layout>
    <x-slot name="title">Schedule Job</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Schedule Job</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.jobs.index') }}">Jobs</a></li>
                        <li class="breadcrumb-item active">Schedule Job</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.jobs.store') }}">
        @csrf
        <div class="row g-3">

            <div class="col-lg-8">

                {{-- Site & Client --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-map-pin-line me-2 text-primary"></i>Site & Client</h6>
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
                                            data-client-id="{{ $site->client_id }}"
                                            data-client-name="{{ $site->client->name ?? '' }}"
                                            {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                        {{ $site->name ?? $site->address }}
                                        @if($site->client) ({{ $site->client->name }}) @endif
                                    </option>
                                    @endforeach
                                </select>
                                @error('site_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Client <span class="text-danger">*</span></label>
                                <input type="text" id="clientDisplay" class="form-control bg-light" readonly
                                       placeholder="Auto-filled from site">
                                <input type="hidden" name="client_id" id="clientId" value="{{ old('client_id') }}">
                                @error('client_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Job Details --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-briefcase-line me-2 text-primary"></i>Job Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Work Type <span class="text-danger">*</span></label>
                                <select name="work_type"
                                        class="form-select @error('work_type') is-invalid @enderror" required>
                                    <option value="">— Select Type —</option>
                                    @foreach(\App\Models\Job::WORK_TYPES as $val => $label)
                                    <option value="{{ $val }}" {{ old('work_type') == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('work_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Scheduled Date</label>
                                <input type="date" name="scheduled_date"
                                       class="form-control @error('scheduled_date') is-invalid @enderror"
                                       value="{{ old('scheduled_date') }}">
                                @error('scheduled_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Scope Notes</label>
                                <textarea name="scope_notes" rows="3"
                                          class="form-control @error('scope_notes') is-invalid @enderror"
                                          placeholder="Describe the scope of work…">{{ old('scope_notes') }}</textarea>
                                @error('scope_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Buildings --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-home-office-line me-2 text-primary"></i>Buildings in Scope</h6>
                    </div>
                    <div class="card-body">
                        <div id="buildingCheckboxes" class="row g-2">
                            <p class="text-muted fs-13 mb-0">Select a site first to load buildings.</p>
                        </div>
                        @error('building_ids')<div class="text-danger fs-12 mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                {{-- Assign Technicians --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-user-star-line me-2 text-primary"></i>Assign Technicians</h6>
                    </div>
                    <div class="card-body">
                        @if($technicians->isEmpty())
                        <p class="text-muted fs-13 mb-0">No field technicians found. Create users with the <em>field-technician</em> role first.</p>
                        @else
                        <div class="vstack gap-2">
                            @foreach($technicians as $tech)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="technician_ids[]" value="{{ $tech->id }}"
                                       id="tech{{ $tech->id }}"
                                       {{ in_array($tech->id, old('technician_ids', [])) ? 'checked' : '' }}>
                                <label class="form-check-label fs-13" for="tech{{ $tech->id }}">
                                    {{ $tech->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        @error('technician_ids')<div class="text-danger fs-12 mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="ri-save-line me-1"></i> Create Job
                    </button>
                    <a href="{{ route('admin.jobs.index') }}" class="btn btn-light">Cancel</a>
                </div>

            </div>
        </div>
    </form>

    @push('scripts')
    <script>
    const buildingsBySite = @json(
        \App\Models\Building::all(['id', 'site_id', 'name_or_level'])
            ->groupBy('site_id')->map(fn ($b) => $b->values())
    );
    const oldBuildings = @json(old('building_ids', []));

    function loadBuildings(siteId) {
        const wrap = document.getElementById('buildingCheckboxes');
        wrap.innerHTML = '';
        const buildings = buildingsBySite[siteId] || [];
        if (!buildings.length) {
            wrap.innerHTML = '<p class="text-muted fs-13 mb-0">No buildings for this site.</p>';
            return;
        }
        buildings.forEach(function (b) {
            const checked = oldBuildings.includes(b.id) ? 'checked' : '';
            wrap.innerHTML += `
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="building_ids[]" value="${b.id}" id="bld${b.id}" ${checked}>
                        <label class="form-check-label fs-13" for="bld${b.id}">${b.name_or_level}</label>
                    </div>
                </div>`;
        });
    }

    document.getElementById('siteSelect').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        document.getElementById('clientId').value      = opt.dataset.clientId || '';
        document.getElementById('clientDisplay').value = opt.dataset.clientName || '';
        loadBuildings(this.value);
    });

    // Pre-populate if old() data exists
    const initSite = document.getElementById('siteSelect').value;
    if (initSite) {
        const opt = document.getElementById('siteSelect').options[document.getElementById('siteSelect').selectedIndex];
        document.getElementById('clientId').value      = opt.dataset.clientId || '';
        document.getElementById('clientDisplay').value = opt.dataset.clientName || '';
        loadBuildings(initSite);
    }
    </script>
    @endpush

</x-app-layout>
