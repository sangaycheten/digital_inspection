<ul class="navbar-nav" id="navbar-nav">

@php
    $user     = auth()->user();
    $roleName = $user?->roles->first()?->name ?? '';

    // System-administrator nav is built entirely from DB (MenuSequence).
    // All other roles read from config/navigation.php.
    if ($roleName === 'system-administrator') {
        $allSeqs = \App\Models\MenuSequence::where('role', 'system-administrator')
            ->where('enabled', true)
            ->orderBy('sequence')
            ->get();

        $topLevel         = $allSeqs->whereNull('parent_key');
        $childrenByParent = $allSeqs->whereNotNull('parent_key')->groupBy('parent_key');

        $navSections    = [];
        $currentSection = null;

        foreach ($topLevel as $seq) {
            if ($seq->section !== $currentSection) {
                $navSections[]  = ['section' => $seq->section, 'items' => []];
                $currentSection = $seq->section;
            }

            $kids = $childrenByParent->has($seq->key)
                ? $childrenByParent->get($seq->key)->map(fn ($c) => $c->toNavItem())->toArray()
                : [];

            $navSections[count($navSections) - 1]['items'][] = $seq->toNavItem($kids);
        }
    } else {
        $navSections = config('navigation.menus.' . $roleName, []);
    }

    // Derive a display label from a nav item.
    // Priority: explicit 'label' → Str::title(permission) → Str::title(first canany perm user holds)
    $navLabel = function (array $item) use ($user): string {
        if (!empty($item['label'])) {
            return $item['label'];
        }
        if (!empty($item['permission'])) {
            return Str::title($item['permission']);
        }
        if (!empty($item['canany'])) {
            $first = collect($item['canany'])->first(fn ($p) => $user?->can($p)) ?? $item['canany'][0];
            return Str::title($first);
        }
        return '';
    };

    // Whether the current user can see a nav item at all.
    $navVisible = function (array $item) use ($user): bool {
        if (!empty($item['canany']))   return $user?->canAny($item['canany']) ?? false;
        if (!empty($item['permission'])) return $user?->can($item['permission']) ?? false;
        return true; // no permission guard → always visible
    };

    // Active-state detection for a child link.
    $childActive = function (array $child): bool {
        // Check extra route patterns (e.g. show/edit pages that should keep parent item highlighted)
        if (!empty($child['active_patterns'])) {
            foreach ($child['active_patterns'] as $p) {
                if (request()->routeIs($p)) return true;
            }
        }

        $pattern = $child['pattern'] ?? null;
        if (!$pattern) return false;

        if (!request()->routeIs($pattern)) return false;

        // active only when NO query params present
        if (!empty($child['no_params'])) {
            return request()->query() === [];
        }

        // active when a specific query param matches (e.g. status=submitted as default)
        if (!empty($child['active_default_status'])) {
            $default = $child['active_default_status'];
            return request('status', $default) === $default;
        }

        // active when specific query params all match
        if (!empty($child['active_params'])) {
            foreach ($child['active_params'] as $key => $value) {
                if (request($key) !== $value) return false;
            }
            return true;
        }

        // active when a specific work_type query param matches
        if (!empty($child['active_work_type'])) {
            return request('work_type') === $child['active_work_type'];
        }

        return true;
    };

    // Active-state detection for a collapsible parent.
    $parentActive = function (array $item) use ($childActive): bool {
        // Explicit pattern on the parent itself
        if (!empty($item['pattern']) && request()->routeIs($item['pattern'])) {
            return true;
        }
        // Extra route patterns (field-tech capture section)
        if (!empty($item['active_patterns'])) {
            foreach ($item['active_patterns'] as $p) {
                if (request()->routeIs($p)) return true;
            }
        }
        // Active when on jobs index with one of the capture work_types
        if (!empty($item['active_work_types']) && request()->routeIs('technician.jobs.index')) {
            if (in_array(request('work_type'), $item['active_work_types'])) return true;
        }
        // Active when any child is active
        foreach ($item['children'] ?? [] as $child) {
            if ($childActive($child)) return true;
        }
        return false;
    };
@endphp

@foreach($navSections as $section)
    <li class="menu-title"><span>{{ $section['section'] }}</span></li>

    @foreach($section['items'] as $item)
        @if($navVisible($item))

            @if(!empty($item['children']))
                {{-- ── Collapsible group ── --}}
                @php
                    $isActive = $parentActive($item);
                    $label    = $navLabel($item);
                    $id       = $item['id'] ?? 'nav_' . md5($label);
                @endphp
                <li class="nav-item">
                    <a class="nav-link menu-link {{ $isActive ? '' : 'collapsed' }}"
                       href="#{{ $id }}"
                       data-bs-toggle="collapse"
                       role="button"
                       aria-expanded="{{ $isActive ? 'true' : 'false' }}"
                       aria-controls="{{ $id }}">
                        <i class="{{ $item['icon'] }}"></i>
                        <span>{{ $label }}</span>
                    </a>
                    <div class="collapse menu-dropdown {{ $isActive ? 'show' : '' }}" id="{{ $id }}">
                        <ul class="nav nav-sm flex-column">
                            @foreach($item['children'] as $child)
                                @if($navVisible($child))
                                    <li class="nav-item">
                                        <a href="{{ route($child['route'], $child['params'] ?? []) }}"
                                           class="nav-link {{ $childActive($child) ? 'active' : '' }}">
                                            @if(!empty($child['icon']))<i class="{{ $child['icon'] }} me-1"></i>@endif
                                            {{ $navLabel($child) }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </li>

            @else
                {{-- ── Single link ── --}}
                @php
                    $label    = $navLabel($item);
                    $pattern  = $item['pattern'] ?? $item['route'] ?? '';
                    $isActive = request()->routeIs($pattern);
                @endphp
                <li class="nav-item">
                    <a class="nav-link menu-link {{ $isActive ? 'active' : '' }}"
                       href="{{ route($item['route'], $item['params'] ?? []) }}">
                        <i class="{{ $item['icon'] }}"></i>
                        <span>{{ $label }}</span>
                    </a>
                </li>

            @endif

        @endif
    @endforeach
@endforeach

{{-- ── Shared bottom: Account ── --}}
<li class="menu-title"><span>Account</span></li>

<li class="nav-item">
    <a class="nav-link menu-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
       href="{{ route('profile.edit') }}">
        <i class="ri-user-line"></i><span>Profile</span>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link menu-link" href="{{ route('logout') }}"
       onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
        <i class="ri-logout-box-line"></i><span>Logout</span>
    </a>
    <form id="sidebar-logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
        @csrf
    </form>
</li>

</ul>
