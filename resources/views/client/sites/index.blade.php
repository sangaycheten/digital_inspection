<x-app-layout>
    <x-slot name="title">My Sites</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">My Sites</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">My Sites</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if($sites->isEmpty())
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="ri-map-pin-line fs-1 opacity-50 d-block mb-2"></i>
            <p class="mb-0">No sites assigned to your account yet.</p>
        </div>
    </div>
    @else
    <div class="row g-3">
        @foreach($sites as $site)
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <h5 class="card-title mb-1">
                                <i class="ri-map-pin-2-line me-2 text-primary"></i>
                                {{ $site->name ?? 'Site' }}
                            </h5>
                            <p class="text-muted fs-13 mb-0">{{ $site->address }}</p>
                        </div>
                        @if($site->client?->custom_client_code)
                        <span class="badge bg-light text-dark border fs-12">{{ $site->client->custom_client_code }}</span>
                        @endif
                    </div>

                    <div class="row g-2 text-center mb-3">
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <div class="fw-semibold fs-16">{{ $site->buildings->count() }}</div>
                                <div class="text-muted fs-12">Buildings</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <div class="fw-semibold fs-16">{{ $assetCounts[$site->id] ?? 0 }}</div>
                                <div class="text-muted fs-12">Assets</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <div class="fw-semibold fs-16 text-truncate" title="{{ $lastJobDates[$site->id] ?? '—' }}">
                                    @if(isset($lastJobDates[$site->id]))
                                        {{ \Carbon\Carbon::parse($lastJobDates[$site->id])->format('M Y') }}
                                    @else
                                        —
                                    @endif
                                </div>
                                <div class="text-muted fs-12">Last Report</div>
                            </div>
                        </div>
                    </div>

                    @if($site->buildings->isNotEmpty())
                    <p class="text-muted fs-12 fw-semibold text-uppercase mb-1">Buildings</p>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($site->buildings as $building)
                        <span class="badge bg-light text-dark border fs-12">{{ $building->name_or_level }}</span>
                        @endforeach
                    </div>
                    @endif

                    @if($site->latitude && $site->longitude)
                    <div class="mt-3">
                        <a href="https://www.google.com/maps?q={{ $site->latitude }},{{ $site->longitude }}"
                           target="_blank" rel="noopener"
                           class="btn btn-sm btn-outline-success">
                            <i class="ri-map-pin-2-line me-1"></i>View on Map
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</x-app-layout>
