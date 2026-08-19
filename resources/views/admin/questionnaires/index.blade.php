<x-app-layout>
    <x-slot name="title">Questionnaires</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Questionnaires</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        
                        <li class="breadcrumb-item active">Questionnaires</li>
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
                        <i class="ri-questionnaire-line me-2 text-primary"></i>All Questionnaires
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $questionnaires->total() }}</span>
                    </h5>
                    <a href="{{ route('admin.questionnaires.create') }}" class="btn btn-sm btn-primary">
                        <i class="ri-add-line me-1"></i> Add Questionnaire
                    </a>
                </div>

                {{-- Filters --}}
                <div class="card-body border-bottom pb-3">
                    <form method="GET" action="{{ route('admin.questionnaires.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label text-muted fs-12 mb-1">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search by name or key..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Asset Type</label>
                            <select name="asset_type" class="form-select form-select-sm">
                                <option value="">All Asset Types</option>
                                @foreach($assetTypes as $val => $label)
                                <option value="{{ $val }}" {{ request('asset_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Section</label>
                            <select name="section_id" class="form-select form-select-sm">
                                <option value="">All Sections</option>
                                @foreach($sections as $sec)
                                <option value="{{ $sec->id }}" {{ request('section_id') === $sec->id ? 'selected' : '' }}>
                                    {{ $sec->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Data Type</label>
                            <select name="type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                @foreach($typeOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-search-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.questionnaires.index') }}" class="btn btn-light btn-sm ms-1">
                                <i class="ri-refresh-line"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width:50px;">#</th>
                                    <th>Question Name</th>
                                    <th style="width:160px;">Key</th>
                                    <th style="width:130px;">Asset Type</th>
                                    <th>Data Type</th>
                                    <th style="width:80px;">Enabled</th>
                                    <th style="width:90px;">Required</th>
                                    <th style="width:90px;">Status</th>
                                    <th style="width:110px;">Created</th>
                                    <th style="width:90px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($questionnaires as $q)
                                @php
                                    $typeEnum  = \App\Enums\DataType::tryFrom($q->type);
                                    $typeBadge = match($q->type) {
                                        'text'              => 'bg-primary-subtle text-primary',
                                        'numeric'           => 'bg-info-subtle text-info',
                                        'switch'            => 'bg-success-subtle text-success',
                                        'option_list'       => 'bg-warning-subtle text-warning',
                                        'long_text'         => 'bg-secondary-subtle text-secondary',
                                        'sub_questionnaire' => 'bg-danger-subtle text-danger',
                                        default             => 'bg-light text-dark',
                                    };
                                @endphp
                                <tr>
                                    <td class="ps-3 text-muted fs-12">{{ $questionnaires->firstItem() + $loop->index }}</td>
                                    <td class="fw-medium">
                                        {{ $q->name }}
                                        @if($q->subQuestionnaires->isNotEmpty())
                                        <div class="fs-11 text-muted mt-1">
                                            <i class="ri-list-check-2 me-1 text-primary"></i>
                                            {{ $q->subQuestionnaires->count() }} sub-question{{ $q->subQuestionnaires->count() > 1 ? 's' : '' }}:
                                            @foreach($q->subQuestionnaires as $sq)
                                                {{ $sq->name }}
                                                @if($sq->condition)
                                                    <span class="badge bg-{{ $sq->condition === 'yes' ? 'success' : 'danger' }}-subtle text-{{ $sq->condition === 'yes' ? 'success' : 'danger' }} px-1">{{ ucfirst($sq->condition) }}</span>
                                                @endif
                                                @if(!$loop->last), @endif
                                            @endforeach
                                        </div>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-light text-dark font-monospace">{{ $q->key }}</span></td>
                                    <td>
                                        @if($q->asset_type)
                                        <span class="badge bg-info-subtle text-info">{{ $assetTypes[$q->asset_type] ?? $q->asset_type }}</span>
                                        @else
                                        <span class="text-muted fs-12">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($q->subQuestionnaires->isNotEmpty())
                                        <span class="badge bg-danger-subtle text-danger">Sub-Questionnaire</span>
                                        @else
                                        <span class="badge {{ $typeBadge }}">{{ $typeEnum?->label() ?? $q->type }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($q->enabled)
                                            <span class="badge bg-success-subtle text-success"><i class="ri-eye-line me-1"></i>Yes</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary"><i class="ri-eye-off-line me-1"></i>No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($q->required)
                                            <span class="badge bg-danger-subtle text-danger"><i class="ri-error-warning-line me-1"></i>Yes</span>
                                        @else
                                            <span class="badge bg-light text-muted">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($q->status === 'active')
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-muted fs-12">{{ $q->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="hstack gap-1">
                                            <a href="{{ route('admin.questionnaires.edit', $q) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Edit">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $q->id }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>

                                        {{-- Delete confirmation modal --}}
                                        <div class="modal fade" id="deleteModal{{ $q->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="avatar-sm mx-auto mb-3">
                                                            <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-22">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </span>
                                                        </div>
                                                        <h5 class="mb-3">Delete Questionnaire</h5>
                                                        <p class="text-muted mb-4">Are you sure you want to delete <strong>{{ $q->name }}</strong>?</p>
                                                        <div class="hstack gap-2 justify-content-center">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST" action="{{ route('admin.questionnaires.destroy', $q) }}">
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
                                    <td colspan="10" class="text-center text-muted py-5">
                                        <i class="ri-questionnaire-line fs-24 d-block mb-2"></i>No questionnaires found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($questionnaires->hasPages())
                <div class="card-footer">{{ $questionnaires->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
