<x-app-layout>
    <x-slot name="title">Schedule Job</x-slot>

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
    @endpush

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
                                            data-timezone="{{ $site->timezone }}"
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
                                @error('work_type')<div class="invalid-feedback">{!! $message !!}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Scheduled Date</label>
                                <input type="date" name="scheduled_date"
                                       class="form-control @error('scheduled_date') is-invalid @enderror"
                                       value="{{ old('scheduled_date') }}">
                                @error('scheduled_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Scheduled Time</label>
                                <input type="time" name="scheduled_time"
                                       class="form-control @error('scheduled_time') is-invalid @enderror"
                                       value="{{ old('scheduled_time') }}">
                                @error('scheduled_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text" id="siteTzHint" style="display:none">
                                    <i class="ri-time-zone-line me-1"></i>Site time: <span id="siteTzLabel"></span>
                                </div>
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

                {{-- Technician-Building Assignment Matrix --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ri-group-line me-2 text-primary"></i>Assign Technicians to Buildings
                            <span class="text-danger">*</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="assignmentMatrix">
                            <p class="text-muted fs-13 mb-0">Select a site first to load the assignment matrix.</p>
                        </div>
                        @error('assignments')<div class="text-danger fs-12 mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

            </div>

            <div class="col-lg-4">
                <div class="d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="ri-save-line me-1"></i> Create Job
                    </button>
                    <a href="{{ route('admin.jobs.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </div>

        </div>
    </form>

    @push('scripts')
    @php
    $techJson = $technicians->map(fn ($t) => [
        'id'    => $t->id,
        'name'  => $t->name,
    ])->values();
    $buildingsJson = \App\Models\Building::all(['id', 'site_id', 'name_or_level'])
        ->groupBy('site_id')->map(fn ($b) => $b->values());
    @endphp
    <script>
    const technicians            = @json($techJson);
    const buildingsBySite        = @json($buildingsJson);
    const oldAssignments         = @json(old('assignments', []));
    const firstInspectedBuildings = @json($firstInspectedBuildingIds);

    function getWorkType() {
        return document.querySelector('[name="work_type"]')?.value || '';
    }

    function loadMatrix(siteId) {
        const wrap = document.getElementById('assignmentMatrix');
        const buildings = buildingsBySite[siteId] || [];
        const isFirstInspection = getWorkType() === 'first_inspection';

        if (!buildings.length) {
            wrap.innerHTML = '<p class="text-muted fs-13 mb-0">No buildings registered for this site. Add buildings first via Master Data.</p>';
            return;
        }
        if (!technicians.length) {
            wrap.innerHTML = '<p class="text-muted fs-13 mb-0">No field technicians found. Create users with the <em>field-technician</em> role first.</p>';
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-bordered align-middle mb-0">';
        html += '<thead class="table-light"><tr>'
            + '<th class="ps-3" style="width:200px">Building</th>'
            + '<th>Assign Technicians</th>'
            + '</tr></thead><tbody>';

        buildings.forEach(b => {
            const alreadyDone = isFirstInspection && firstInspectedBuildings.includes(b.id);
            const oldTechs = oldAssignments[b.id] || [];
            let cell = '';
            if (alreadyDone) {
                cell = '<span class="badge bg-success-subtle text-success fs-12"><i class="ri-checkbox-circle-line me-1"></i>First Inspection Completed</span>';
            } else {
                const options = technicians.map(t => {
                    const selected = oldTechs.includes(t.id) ? 'selected' : '';
                    return `<option value="${t.id}" ${selected}>${t.name}</option>`;
                }).join('');
                cell = `<select name="assignments[${b.id}][]"
                                id="techSelect_${b.id}"
                                class="form-select"
                                multiple>${options}</select>`;
            }
            html += `<tr>
                <td class="ps-3 fw-medium fs-13">${b.name_or_level}</td>
                <td>${cell}</td>
            </tr>`;
        });

        html += '</tbody></table></div>';
        wrap.innerHTML = html;

        // Init Tom Select on each generated select
        buildings.forEach(b => {
            const el = document.getElementById(`techSelect_${b.id}`);
            if (el) {
                new TomSelect(el, {
                    plugins: ['remove_button'],
                    placeholder: 'Select technicians...',
                    create: false,
                    maxOptions: null,
                });
            }
        });
    }

    function getTzAbbr(tz) {
        try {
            return new Intl.DateTimeFormat('en', { timeZone: tz, timeZoneName: 'short' })
                .formatToParts(new Date()).find(p => p.type === 'timeZoneName')?.value || tz;
        } catch(e) { return tz; }
    }

    function updateSiteTzHint(tz) {
        const hint  = document.getElementById('siteTzHint');
        const label = document.getElementById('siteTzLabel');
        if (tz) {
            label.textContent = getTzAbbr(tz) + ' (' + tz + ')';
            hint.style.display = '';
        } else {
            hint.style.display = 'none';
        }
    }

    document.getElementById('siteSelect').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        document.getElementById('clientId').value      = opt.dataset.clientId || '';
        document.getElementById('clientDisplay').value = opt.dataset.clientName || '';
        updateSiteTzHint(opt.dataset.timezone || '');
        loadMatrix(this.value);
    });

    document.querySelector('[name="work_type"]').addEventListener('change', function () {
        const siteId = document.getElementById('siteSelect').value;
        if (siteId) loadMatrix(siteId);
    });

    const initSite = document.getElementById('siteSelect').value;
    if (initSite) {
        const opt = document.getElementById('siteSelect').options[document.getElementById('siteSelect').selectedIndex];
        document.getElementById('clientId').value      = opt.dataset.clientId || '';
        document.getElementById('clientDisplay').value = opt.dataset.clientName || '';
        updateSiteTzHint(opt.dataset.timezone || '');
        loadMatrix(initSite);
    }

    </script>
    @endpush

</x-app-layout>
