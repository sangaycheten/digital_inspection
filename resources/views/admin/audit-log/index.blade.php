<x-app-layout>
    <x-slot name="title">Audit Log</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Audit Log</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Audit Log</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-history-line me-2 text-primary"></i>Activity Log
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $logs->total() }}</span>
                    </h5>
                </div>

                {{-- Filters --}}
                <div class="card-body border-bottom pb-3">
                    <form method="GET" action="{{ route('admin.audit-log.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label text-muted fs-12 mb-1">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search description, event, or model..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Log Type</label>
                            <select name="log" class="form-select form-select-sm">
                                <option value="">All Logs</option>
                                <option value="auth"   {{ request('log') === 'auth'   ? 'selected' : '' }}>Auth</option>
                                <option value="user"   {{ request('log') === 'user'   ? 'selected' : '' }}>Users</option>
                                <option value="rbac"   {{ request('log') === 'rbac'   ? 'selected' : '' }}>RBAC</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Event Type</label>
                            <select name="event" class="form-select form-select-sm">
                                <option value="">All Events</option>
                                <option value="login"    {{ request('event') === 'login'    ? 'selected' : '' }}>Login</option>
                                <option value="logout"   {{ request('event') === 'logout'   ? 'selected' : '' }}>Logout</option>
                                <option value="created"  {{ request('event') === 'created'  ? 'selected' : '' }}>Created</option>
                                <option value="updated"  {{ request('event') === 'updated'  ? 'selected' : '' }}>Updated</option>
                                <option value="deleted"  {{ request('event') === 'deleted'  ? 'selected' : '' }}>Deleted</option>
                                <option value="restored" {{ request('event') === 'restored' ? 'selected' : '' }}>Restored</option>
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-search-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.audit-log.index') }}" class="btn btn-light btn-sm ms-1">
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
                                    <th class="ps-3">#</th>
                                    <th>Type</th>
                                    <th>Event</th>
                                    <th>Description</th>
                                    <th>Subject</th>
                                    <th>Performed By</th>
                                    <th>Date & Time</th>
                                    <th>Changes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td class="ps-3 text-muted fs-12">{{ $logs->firstItem() + $loop->index }}</td>
                                    <td>
                                        @php
                                            $logColor = match($log->log_name) {
                                                'auth' => 'info',
                                                'user' => 'primary',
                                                'rbac' => 'warning',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $logColor }}-subtle text-{{ $logColor }}">
                                            {{ strtoupper($log->log_name ?? '—') }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $eventColor = match($log->event) {
                                                'login'    => 'success',
                                                'logout'   => 'secondary',
                                                'created'  => 'success',
                                                'updated'  => 'primary',
                                                'deleted'  => 'danger',
                                                'restored' => 'warning',
                                                default    => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $eventColor }}-subtle text-{{ $eventColor }}">
                                            {{ ucfirst($log->event ?? '—') }}
                                        </span>
                                    </td>
                                    <td>{{ $log->description }}</td>
                                    <td>
                                        @if($log->subject_type)
                                            <span class="text-muted fs-12">
                                                {{ class_basename($log->subject_type) }}
                                                @if($log->subject_id)
                                                    <span class="badge bg-light text-dark ms-1">#{{ $log->subject_id }}</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->causer)
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-xs flex-shrink-0">
                                                    <span class="avatar-title rounded-circle bg-primary-subtle text-primary fs-12">
                                                        {{ strtoupper(substr($log->causer->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fs-13">{{ $log->causer->name }}</div>
                                                    <div class="text-muted fs-11">{{ $log->causer->email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted fs-12">System</span>
                                        @endif
                                    </td>
                                    <td class="text-muted fs-12">
                                        <div>{{ $log->created_at->format('d M Y') }}</div>
                                        <div>{{ $log->created_at->format('H:i:s') }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $added   = $log->properties->get('added', []);
                                            $removed = $log->properties->get('removed', []);
                                            $changes = $log->properties->get('attributes', []);
                                            $hasDetail = count($added) || count($removed) || count($changes);
                                        @endphp
                                        @if($hasDetail)
                                            <button type="button"
                                                    class="btn btn-xs btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#changesModal{{ $log->id }}">
                                                <i class="ri-eye-line me-1"></i> View
                                            </button>

                                            <div class="modal fade" id="changesModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">
                                                                Changes — {{ class_basename($log->subject_type ?? '') }} #{{ $log->subject_id }}
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            {{-- Permission changes (RBAC) --}}
                                                            @if(count($added) || count($removed))
                                                                @if(count($added))
                                                                <p class="fw-medium mb-1 text-success">
                                                                    <i class="ri-add-circle-line me-1"></i> Permissions Added
                                                                </p>
                                                                <div class="mb-3 d-flex flex-wrap gap-1">
                                                                    @foreach($added as $perm)
                                                                        <span class="badge bg-success-subtle text-success">{{ $perm }}</span>
                                                                    @endforeach
                                                                </div>
                                                                @endif
                                                                @if(count($removed))
                                                                <p class="fw-medium mb-1 text-danger">
                                                                    <i class="ri-delete-bin-line me-1"></i> Permissions Removed
                                                                </p>
                                                                <div class="d-flex flex-wrap gap-1">
                                                                    @foreach($removed as $perm)
                                                                        <span class="badge bg-danger-subtle text-danger">{{ $perm }}</span>
                                                                    @endforeach
                                                                </div>
                                                                @endif
                                                            @else
                                                            {{-- Field changes (User model etc.) --}}
                                                            <table class="table table-sm table-bordered mb-0">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Field</th>
                                                                        <th>Old Value</th>
                                                                        <th>New Value</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($changes as $field => $newValue)
                                                                    @if(!is_array($newValue))
                                                                    <tr>
                                                                        <td class="fw-medium">{{ $field }}</td>
                                                                        <td class="text-muted">{{ $log->properties->get('old')[$field] ?? '—' }}</td>
                                                                        <td class="text-success">{{ $newValue }}</td>
                                                                    </tr>
                                                                    @endif
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted fs-12">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="ri-history-line fs-24 d-block mb-2"></i>
                                        No activity recorded yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($logs->hasPages())
                <div class="card-footer">
                    {{ $logs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
