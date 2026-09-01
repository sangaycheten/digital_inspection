<x-app-layout>
    <x-slot name="title">Menu Elements</x-slot>

    @push('styles')
    <style>
        .menu-item-card { border: 1px solid var(--vz-border-color); border-radius: 8px; margin-bottom: 6px; overflow: hidden; }
        .menu-item-header { display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: var(--vz-secondary-bg); cursor: pointer; user-select: none; }
        .menu-item-header:hover { background: var(--vz-tertiary-bg); }
        .menu-item-header .seq-badge { min-width: 26px; text-align: center; }
        .menu-children { border-top: 1px solid var(--vz-border-color); }
        .menu-child-row { display: flex; align-items: center; gap: 10px; padding: 8px 14px 8px 40px; border-bottom: 1px solid var(--vz-border-color); background: var(--vz-body-bg); }
        .menu-child-row:last-child { border-bottom: none; }
        .menu-child-row:hover { background: var(--vz-tertiary-bg); }
        .menu-btn-group { display: flex; gap: 4px; margin-left: auto; flex-shrink: 0; }
        .toggle-icon { transition: transform .2s; }
        .collapsed .toggle-icon { transform: rotate(-90deg); }
    </style>
    @endpush

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Menu Elements</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Menu Elements</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-border-left alert-dismissible fade show">
        <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-menu-2-line me-2 text-primary"></i>
                        Sidebar Menu Order
                        <span class="badge bg-primary-subtle text-primary ms-1">System Administrator</span>
                    </h5>
                </div>
                <div class="card-body p-3">

                    @foreach($topLevel as $item)
                    @php $subs = $children->get($item->key, collect()); @endphp

                    <div class="menu-item-card">

                        {{-- Parent row --}}
                        <div class="menu-item-header {{ $subs->isNotEmpty() ? '' : '' }}"
                             @if($subs->isNotEmpty()) onclick="toggleChildren('sub_{{ $item->key }}')" @endif>

                            <span class="badge bg-secondary-subtle text-secondary seq-badge fs-12">{{ $item->sequence }}</span>
                            <i class="{{ $item->icon }} text-primary fs-16"></i>
                            <span class="fw-semibold fs-14 flex-grow-1">{{ $item->label }}</span>

                            @if($subs->isNotEmpty())
                            <span class="badge bg-light text-muted border fs-11">
                                <i class="ri-list-unordered me-1"></i>{{ $subs->count() }} sub-items
                            </span>
                            <i class="ri-arrow-down-s-line fs-16 text-muted toggle-icon" id="icon_{{ $item->key }}"></i>
                            @endif

                            <div class="menu-btn-group" onclick="event.stopPropagation()">
                                <form method="POST" action="{{ route('admin.menu.moveUp', $item) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success {{ $loop->first ? 'disabled' : '' }}"
                                            @if($loop->first) tabindex="-1" @endif title="Move up">
                                        <i class="ri-arrow-up-line"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.menu.moveDown', $item) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success {{ $loop->last ? 'disabled' : '' }}"
                                            @if($loop->last) tabindex="-1" @endif title="Move down">
                                        <i class="ri-arrow-down-line"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Sub-menu rows --}}
                        @if($subs->isNotEmpty())
                        <div class="menu-children collapse show" id="sub_{{ $item->key }}">
                            @foreach($subs as $child)
                            <div class="menu-child-row">
                                <span class="badge bg-light text-muted border seq-badge fs-11">{{ $child->sequence }}</span>
                                <i class="{{ $child->icon }} text-secondary fs-14"></i>
                                <span class="fs-13 flex-grow-1">{{ $child->label }}</span>
                                <div class="menu-btn-group">
                                    <form method="POST" action="{{ route('admin.menu.moveUp', $child) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary {{ $loop->first ? 'disabled' : '' }}"
                                                @if($loop->first) tabindex="-1" @endif title="Move up">
                                            <i class="ri-arrow-up-line"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.menu.moveDown', $child) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary {{ $loop->last ? 'disabled' : '' }}"
                                                @if($loop->last) tabindex="-1" @endif title="Move down">
                                            <i class="ri-arrow-down-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                    </div>
                    @endforeach

                </div>
                <div class="card-footer text-muted fs-12">
                    <i class="ri-information-line me-1"></i>
                    Click a menu item to expand/collapse its sub-items. Changes take effect immediately.
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleChildren(id) {
            const el   = document.getElementById(id);
            const key  = id.replace('sub_', '');
            const icon = document.getElementById('icon_' + key);
            if (!el) return;
            el.classList.toggle('show');
            if (icon) icon.style.transform = el.classList.contains('show') ? '' : 'rotate(-90deg)';
        }
    </script>
    @endpush

</x-app-layout>
