@use('Illuminate\Support\Facades\Storage')
<x-app-layout>
    <x-slot name="title">Hierarchy</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Hierarchy</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item active">Hierarchy</li>
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

    @if($managers->isEmpty())
    <div class="alert alert-warning alert-border-left fs-13">
        <i class="ri-alert-line me-2"></i>No users with the <strong>manager</strong> role found.
        <a href="{{ route('admin.rbac.index') }}">Assign the manager role</a> to a user first.
    </div>
    @endif

    <div class="accordion" id="hierarchyAccordion">
        @forelse($clients as $index => $client)
        @php $collapseId = 'client-' . $client->id; @endphp

        <div class="card mb-3 shadow-sm border">

            {{-- ── Client header: toggle (left) + manager/assign (right) ── --}}
            {{-- Using a flex row OUTSIDE the <button> to avoid nested-button invalid HTML --}}
            <div class="d-flex align-items-stretch" style="min-height:64px;">

                {{-- Expand / collapse toggle (this is the only <button> in this row) --}}
                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }} flex-grow-1 border-0 bg-transparent text-start py-3 ps-3"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#{{ $collapseId }}"
                        aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                        style="box-shadow:none;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle text-primary rounded fs-18">
                                <i class="ri-building-2-line"></i>
                            </span>
                        </div>
                        <div>
                            <div class="fw-semibold fs-15">{{ $client->name }}</div>
                            <div class="text-muted fs-12 d-flex align-items-center gap-2 flex-wrap">
                                @if($client->custom_client_code)
                                <span class="font-monospace">{{ $client->custom_client_code }}</span>
                                @endif
                                <span class="badge {{ $client->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                    {{ ucfirst($client->status) }}
                                </span>
                                <span><i class="ri-map-pin-line me-1"></i>{{ $client->sites->count() }} site{{ $client->sites->count() !== 1 ? 's' : '' }}</span>
                            </div>
                        </div>
                    </div>
                </button>

                {{-- Manager info + Assign — sibling div, NOT inside the button --}}
                <div class="d-flex align-items-center gap-3 pe-3 border-start ps-3 flex-shrink-0">
                    @if($client->manager)
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $client->manager->avatar
                            ? Storage::disk('public')->url($client->manager->avatar)
                            : asset('assets/images/users/avatar-1.jpg') }}"
                            class="rounded-circle" style="width:32px;height:32px;object-fit:cover;" alt="">
                        <div class="d-none d-lg-block">
                            <div class="fw-medium fs-13">{{ $client->manager->name }}</div>
                            <div class="text-muted fs-12">{{ $client->manager->email }}</div>
                        </div>
                    </div>
                    @else
                    <span class="text-warning fs-13 d-none d-md-inline">
                        <i class="ri-user-unfollow-line me-1"></i>Unassigned
                    </span>
                    @endif

                    <button type="button"
                            class="btn btn-sm btn-outline-primary flex-shrink-0"
                            onclick="openAssignModal('{{ $client->id }}', '{{ addslashes($client->name) }}', '{{ $client->manager_id ?? '' }}')">
                        <i class="ri-user-settings-line me-1"></i>Assign
                    </button>
                </div>
            </div>

            {{-- ── Collapsible body: sites → buildings ────────────────────── --}}
            <div id="{{ $collapseId }}"
                 class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                 data-bs-parent="#hierarchyAccordion">
                <div class="card-body p-0 border-top">

                    @if($client->sites->isEmpty())
                    <div class="text-muted fs-13 px-4 py-3">
                        <i class="ri-map-pin-line me-1"></i>No sites registered for this client.
                        <a href="{{ route('admin.master.sites.index') }}" class="ms-1">Add a site</a>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width:32px;"></th>
                                    <th>Site</th>
                                    <th>Address</th>
                                    <th>Buildings</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($client->sites as $site)
                                {{-- Site row --}}
                                <tr class="table-active">
                                    <td class="ps-4 text-primary"><i class="ri-map-pin-2-line"></i></td>
                                    <td class="fw-medium fs-13">{{ $site->name ?? $site->address }}</td>
                                    <td class="text-muted fs-13">{{ $site->address ?? '—' }}</td>
                                    <td class="fs-13 text-muted">
                                        {{ $site->buildings->count() }} building{{ $site->buildings->count() !== 1 ? 's' : '' }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.master.buildings.index', ['site_id' => $site->id]) }}"
                                           class="btn btn-xs btn-outline-secondary py-0 px-2 fs-12">
                                            <i class="ri-eye-line me-1"></i>View Buildings
                                        </a>
                                    </td>
                                </tr>

                                {{-- Building rows --}}
                                @foreach($site->buildings as $building)
                                <tr>
                                    <td class="ps-4"></td>
                                    <td class="ps-5 fs-13 text-muted">
                                        <i class="ri-corner-down-right-line me-1 text-muted"></i>{{ $building->name_or_level }}
                                    </td>
                                    <td class="fs-12 text-muted">
                                        @if($building->roof_zones)
                                        <span class="badge bg-light text-dark border">
                                            {{ count($building->roof_zones) }} zone{{ count($building->roof_zones) !== 1 ? 's' : '' }}
                                        </span>
                                        @else
                                        —
                                        @endif
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">No clients found.</div>
        @endforelse
    </div>

    {{-- Assign Manager Modal --}}
    <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content">
                <form method="POST" id="assignForm">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ri-user-settings-line me-2 text-primary"></i>Assign Manager</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted fs-13 mb-3">
                            The selected manager will review all inspection records for
                            <strong id="modalClientName"></strong>'s assets.
                        </p>
                        <label class="form-label fw-medium">Manager</label>
                        <select name="manager_id" id="modalManagerSelect" class="form-select">
                            <option value="">— Unassigned —</option>
                            @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->name }} ({{ $manager->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i>Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    const BASE_URL = "{{ url('admin/master/hierarchy') }}";

    function openAssignModal(clientId, clientName, currentManagerId) {
        document.getElementById('modalClientName').textContent = clientName;
        document.getElementById('assignForm').action = BASE_URL + '/' + clientId;
        document.getElementById('modalManagerSelect').value = currentManagerId || '';
        new bootstrap.Modal(document.getElementById('assignModal')).show();
    }
    </script>
    @endpush
</x-app-layout>
