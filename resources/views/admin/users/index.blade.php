<x-app-layout>
    <x-slot name="title">Users</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Users</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">User & Role Management</li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Users --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        All Users
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $users->total() }}</span>
                    </h5>
                    @can('add users')
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                        <i class="ri-user-add-line align-middle me-1"></i> Add User
                    </a>
                    @endcan
                </div>
                <div class="card-body border-bottom pb-3">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label text-muted fs-12 mb-1">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search by name or email..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fs-12 mb-1">Role</label>
                            <select name="role" class="form-select form-select-sm">
                                <option value="">All Roles</option>
                                <option value="system-administrator" {{ request('role') === 'system-administrator' ? 'selected' : '' }}>System Administrator</option>
                                <option value="manager"              {{ request('role') === 'manager'              ? 'selected' : '' }}>Manager</option>
                                <option value="field-technician"     {{ request('role') === 'field-technician'     ? 'selected' : '' }}>Field Technician</option>
                                <option value="client-user"          {{ request('role') === 'client-user'          ? 'selected' : '' }}>Client User</option>
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-search-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm ms-1">
                                <i class="ri-refresh-line"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-checkbox-circle-line me-2 align-middle"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-border-left alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-2 align-middle"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-borderless table-nowrap align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Site</th>
                                    <th>Verified</th>
                                    <th>Password</th>
                                    <th>Created</th>
                                    <th>Last Edited</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr>
                                        <td>{{ $users->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-xs flex-shrink-0">
                                                    <span class="avatar-title rounded-circle bg-primary-subtle text-primary fs-12">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                                {{ $user->name }}
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @forelse($user->roles as $role)
                                                <span class="badge
                                                    @if($role->name === 'system-administrator') bg-danger-subtle text-danger
                                                    @elseif($role->name === 'manager') bg-warning-subtle text-warning
                                                    @elseif($role->name === 'field-technician') bg-primary-subtle text-primary
                                                    @elseif($role->name === 'client-user') bg-success-subtle text-success
                                                    @else bg-secondary-subtle text-secondary
                                                    @endif">
                                                    {{ role_label($role->name) }}
                                                </span>
                                            @empty
                                                <span class="badge bg-secondary-subtle text-secondary">No Role</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            @if($user->hasRole('client-user') && $user->client)
                                                @php $siteCount = $user->sites->count(); @endphp
                                                <span class="badge bg-info-subtle text-info">{{ $siteCount }} {{ Str::plural('Site', $siteCount) }}</span>
                                            @elseif($user->hasRole('field-technician') && $user->sites->isNotEmpty())
                                                @php $siteCount = $user->sites->count(); @endphp
                                                <span class="badge bg-secondary-subtle text-secondary">{{ $siteCount }} {{ Str::plural('Site', $siteCount) }}</span>
                                            @else
                                                <span class="text-muted fs-12">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($user->email_verified_at)
                                                <span class="badge bg-success-subtle text-success">Verified</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning">Unverified</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($user->has_password)
                                                <span class="badge bg-success-subtle text-success" title="Password has been set">
                                                    <i class="ri-lock-password-line me-1"></i>Set
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger" title="No password — send credentials to enable login">
                                                    <i class="ri-lock-unlock-line me-1"></i>Not Set
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fs-12">{{ $user->created_at->format('d M Y, H:i') }}</div>
                                            @if($user->creator)
                                                <div class="text-muted fs-11">by {{ $user->creator->name }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->updated_at && $user->updated_at->ne($user->created_at))
                                                <div class="fs-12">{{ $user->updated_at->format('d M Y, H:i') }}</div>
                                                @if($user->editor)
                                                    <div class="text-muted fs-11">by {{ $user->editor->name }}</div>
                                                @endif
                                            @else
                                                <span class="text-muted fs-12">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="hstack gap-2">
                                                @can('edit users')
                                                <a href="{{ route('admin.users.edit', $user) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-sm {{ $user->credentials_sent_at ? 'btn-info' : 'btn-outline-info' }}"
                                                        title="{{ $user->credentials_sent_at ? 'Credentials sent ' . $user->credentials_sent_at->diffForHumans() : 'Send Credentials' }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#credentialsModal{{ $user->id }}">
                                                    <i class="ri-mail-send-line"></i>
                                                </button>
                                                @endcan
                                                @can('delete users')
                                                @if($user->id !== request()->user()?->id)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal{{ $user->id }}">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                                @endif
                                                @endcan
                                            </div>

                                            {{-- Send Credentials Modal --}}
                                            <div class="modal fade" id="credentialsModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">
                                                                <i class="ri-mail-send-line me-2 text-info"></i>Reset &amp; Send New Credentials
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="text-muted fs-13 mb-3">The following email will be sent to the user:</p>

                                                            <div class="border rounded p-3 bg-light">
                                                                <div class="mb-2 d-flex gap-2">
                                                                    <span class="text-muted fs-12" style="min-width:60px;">To</span>
                                                                    <span class="fw-medium fs-13">{{ $user->email }}</span>
                                                                </div>
                                                                <div class="mb-3 d-flex gap-2">
                                                                    <span class="text-muted fs-12" style="min-width:60px;">Subject</span>
                                                                    <span class="fs-13">Your Login Credentials – {{ config('app.name') }}</span>
                                                                </div>
                                                                <hr class="my-2">
                                                                <div class="fs-13 text-muted mb-2">Email body will include:</div>
                                                                <ul class="fs-13 mb-0 ps-3">
                                                                    <li>Greeting to <strong>{{ $user->name }}</strong></li>
                                                                    <li>Login email: <span class="font-monospace">{{ $user->email }}</span></li>
                                                                    <li>A <strong>new</strong> auto-generated temporary password</li>
                                                                    <li>Login link to the portal</li>
                                                                </ul>
                                                            </div>

                                                            @if($user->credentials_sent_at)
                                                            <div class="alert alert-info alert-border-left mt-3 mb-0 py-2">
                                                                <i class="ri-time-line me-1"></i>
                                                                <small>Last sent <strong>{{ $user->credentials_sent_at->diffForHumans() }}</strong> — {{ $user->credentials_sent_at->format('d M Y, H:i') }}</small>
                                                            </div>
                                                            @endif
                                                            <div class="alert alert-danger alert-border-left mt-2 mb-0 py-2">
                                                                <i class="ri-alert-line me-1"></i>
                                                                <small>This will <strong>replace the user's current password</strong> with a new randomly generated one.</small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST" action="{{ route('admin.users.send-credentials', $user) }}">
                                                                @csrf
                                                                <button type="submit" class="btn btn-info">
                                                                    <i class="ri-send-plane-line me-1"></i> Reset &amp; Send
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if($user->id !== request()->user()?->id)
                                            <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                                    <div class="modal-content">
                                                        <div class="modal-body text-center p-4">
                                                            <div class="avatar-sm mx-auto mb-3">
                                                                <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-22">
                                                                    <i class="ri-delete-bin-line"></i>
                                                                </span>
                                                            </div>
                                                            <h5 class="mb-3">Delete User</h5>
                                                            <p class="text-muted mb-4">
                                                                Are you sure you want to delete <strong>{{ $user->name }}</strong>?
                                                            </p>
                                                            <div class="hstack gap-2 justify-content-center">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">No users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Deleted Users --}}
    @if($trashed->isNotEmpty())
    <div class="row">
        <div class="col-12">
            <div class="card border border-danger-subtle">
                <div class="card-header d-flex align-items-center bg-danger-subtle">
                    <h5 class="card-title mb-0 flex-grow-1 text-danger">
                        <i class="ri-delete-bin-line me-2"></i>Deleted Users
                        <span class="badge bg-danger ms-1">{{ $trashed->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-borderless table-nowrap align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Deleted On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trashed as $user)
                                <tr class="text-muted">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-xs flex-shrink-0">
                                                <span class="avatar-title rounded-circle bg-danger-subtle text-danger fs-12">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <span class="text-decoration-line-through">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @forelse($user->roles as $role)
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                {{ role_label($role->name) }}
                                            </span>
                                        @empty
                                            <span class="badge bg-secondary-subtle text-secondary">No Role</span>
                                        @endforelse
                                    </td>
                                    <td>{{ $user->deleted_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        @can('edit users')
                                        <form method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                <i class="ri-restart-line me-1"></i> Restore
                                            </button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</x-app-layout>
