<x-app-layout>
    <x-slot name="title">Edit Job</x-slot>

    @php
    $statusColors = [
        'new'                    => 'secondary',
        'scheduled'              => 'info',
        'in_progress'            => 'primary',
        'submitted_for_review'   => 'warning',
        'under_review'           => 'warning',
        'approved'               => 'success',
        'issued'                 => 'success',
        'rectification_required' => 'danger',
        'closed'                 => 'dark',
    ];
    $sc = $statusColors[$job->status] ?? 'secondary';
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Edit Job</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.jobs.index') }}">Jobs</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.jobs.show', $job) }}">Job</a></li>
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

    <form method="POST" action="{{ route('admin.jobs.update', $job) }}">
        @csrf @method('PUT')
        <div class="row g-3">

            <div class="col-lg-8">

                {{-- Site (read-only on edit) --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-map-pin-line me-2 text-primary"></i>Site & Client</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Site</label>
                                <input type="text" class="form-control bg-light" readonly
                                       value="{{ $job->site->name ?? $job->site->address }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Client</label>
                                <input type="text" class="form-control bg-light" readonly
                                       value="{{ $job->client->name ?? '—' }}">
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
                                    @foreach(\App\Models\Job::WORK_TYPES as $val => $label)
                                    <option value="{{ $val }}" {{ old('work_type', $job->work_type) == $val ? 'selected' : '' }}>
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
                                       value="{{ old('scheduled_date', $job->scheduled_date?->format('Y-m-d')) }}">
                                @error('scheduled_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Scope Notes</label>
                                <textarea name="scope_notes" rows="3"
                                          class="form-control @error('scope_notes') is-invalid @enderror">{{ old('scope_notes', $job->scope_notes) }}</textarea>
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
                        @if($buildings->isEmpty())
                        <p class="text-muted fs-13 mb-0">No buildings for this site.</p>
                        @else
                        <div class="row g-2">
                            @foreach($buildings as $building)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="building_ids[]" value="{{ $building->id }}"
                                           id="bld{{ $building->id }}"
                                           {{ in_array($building->id, old('building_ids', $job->buildings->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    <label class="form-check-label fs-13" for="bld{{ $building->id }}">
                                        {{ $building->name_or_level }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                {{-- Status transition --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-git-branch-line me-2 text-primary"></i>Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} fs-13 px-3 py-2">
                                {{ \App\Models\Job::STATUSES[$job->status] }}
                            </span>
                        </div>
                        @php $nextStatuses = $job->nextStatuses(); @endphp
                        @if($nextStatuses)
                        <label class="form-label mt-2">Advance to</label>
                        <select name="status"
                                class="form-select @error('status') is-invalid @enderror">
                            <option value="{{ $job->status }}">— Keep current —</option>
                            @foreach($nextStatuses as $ns)
                            <option value="{{ $ns }}" {{ old('status') == $ns ? 'selected' : '' }}>
                                {{ \App\Models\Job::STATUSES[$ns] }}
                            </option>
                            @endforeach
                        </select>
                        @else
                        <input type="hidden" name="status" value="{{ $job->status }}">
                        <p class="text-muted fs-12 mt-2 mb-0">No further transitions available.</p>
                        @endif
                        @error('status')<div class="text-danger fs-12 mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Assign Technicians --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="ri-user-star-line me-2 text-primary"></i>Technicians</h6>
                    </div>
                    <div class="card-body">
                        @php $assignedIds = $job->technicians->pluck('id')->toArray(); @endphp
                        @if($technicians->isEmpty())
                        <p class="text-muted fs-13 mb-0">No field technicians found.</p>
                        @else
                        <div class="vstack gap-2">
                            @foreach($technicians as $tech)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="technician_ids[]" value="{{ $tech->id }}"
                                       id="tech{{ $tech->id }}"
                                       {{ in_array($tech->id, old('technician_ids', $assignedIds)) ? 'checked' : '' }}>
                                <label class="form-check-label fs-13" for="tech{{ $tech->id }}">
                                    {{ $tech->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="ri-save-line me-1"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.jobs.show', $job) }}" class="btn btn-light">Cancel</a>
                </div>

            </div>
        </div>
    </form>

</x-app-layout>
