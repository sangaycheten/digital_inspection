<x-app-layout>
    <x-slot name="title">Client Feedback</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Client Feedback</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Client Feedback</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Aggregate stats --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center py-4">
                    <p class="text-muted fs-12 text-uppercase fw-semibold mb-1">Overall Average</p>
                    <h1 class="fw-bold text-warning mb-1">{{ $averages->overall ?? '—' }}</h1>
                    <div class="fs-20 mb-1">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="ri-star-{{ $i <= round($averages->overall ?? 0) ? 'fill' : 'line' }} text-warning"></i>
                        @endfor
                    </div>
                    <p class="text-muted fs-13 mb-0">Based on {{ $averages->total ?? 0 }} review(s)</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted fs-12 text-uppercase fw-semibold mb-3">Category Averages</p>
                    @foreach([
                        'service_quality' => 'Service Quality',
                        'communication'   => 'Communication',
                        'timeliness'      => 'Timeliness',
                        'professionalism' => 'Professionalism',
                    ] as $field => $label)
                    @php $avg = $averages->$field ?? null; $pct = $avg ? ($avg / 5) * 100 : 0; @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between fs-13 mb-1">
                            <span class="text-muted">{{ $label }}</span>
                            <span class="fw-medium">{{ $avg ?? '—' }}</span>
                        </div>
                        <div class="progress" style="height:5px">
                            <div class="progress-bar bg-warning" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted fs-12 text-uppercase fw-semibold mb-3">Rating Distribution</p>
                    @for($i = 5; $i >= 1; $i--)
                    @php
                        $cnt  = $distribution[$i] ?? 0;
                        $total = $averages->total ?: 1;
                        $pct  = round($cnt / $total * 100);
                    @endphp
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="text-muted fs-12" style="width:20px">{{ $i }}</span>
                        <i class="ri-star-fill text-warning fs-12"></i>
                        <div class="progress flex-grow-1" style="height:8px">
                            <div class="progress-bar bg-warning" style="width:{{ $pct }}%"></div>
                        </div>
                        <span class="text-muted fs-12" style="width:24px">{{ $cnt }}</span>
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    {{-- Filters + table --}}
    <div class="card">
        <div class="card-header border-bottom pb-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-muted fs-12 mb-1">Client</label>
                    <select name="client_id" class="form-select form-select-sm">
                        <option value="">All Clients</option>
                        @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted fs-12 mb-1">Rating</label>
                    <select name="rating" class="form-select form-select-sm">
                        <option value="">All Ratings</option>
                        @foreach([5,4,3,2,1] as $r)
                        <option value="{{ $r }}" {{ request('rating') == $r ? 'selected' : '' }}>{{ $r }} Star</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i>Filter</button>
                    <a href="{{ route('admin.feedback.index') }}" class="btn btn-light btn-sm ms-1"><i class="ri-refresh-line"></i></a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Client / User</th>
                            <th>Job</th>
                            <th>Overall</th>
                            <th>Quality</th>
                            <th>Comm.</th>
                            <th>Time</th>
                            <th>Prof.</th>
                            <th>Comment</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feedbacks as $fb)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium fs-13">{{ $fb->client->name ?? '—' }}</div>
                                <div class="text-muted fs-12">{{ $fb->user->name ?? '—' }}</div>
                            </td>
                            <td class="fs-12 text-muted">
                                @if($fb->job)
                                    {{ $fb->job->site->name ?? $fb->job->site->address ?? '—' }}
                                    <span class="badge bg-info-subtle text-info fs-11 ms-1">
                                        {{ \App\Models\Job::WORK_TYPES[$fb->job->work_type] }}
                                    </span>
                                @else
                                    <span class="text-muted">General</span>
                                @endif
                            </td>
                            <td>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="ri-star-{{ $i <= $fb->overall_rating ? 'fill' : 'line' }} text-warning fs-13"></i>
                                @endfor
                            </td>
                            @foreach(['service_quality','communication','timeliness','professionalism'] as $field)
                            <td class="text-center fs-13">
                                @if($fb->$field)
                                    <span class="badge bg-{{ $fb->$field >= 4 ? 'success' : ($fb->$field >= 3 ? 'warning' : 'danger') }}-subtle
                                                 text-{{ $fb->$field >= 4 ? 'success' : ($fb->$field >= 3 ? 'warning' : 'danger') }}">
                                        {{ $fb->$field }}/5
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            @endforeach
                            <td class="fs-12" style="max-width:200px">
                                @if($fb->message)
                                <span class="text-muted" title="{{ $fb->message }}">
                                    {{ Str::limit($fb->message, 60) }}
                                </span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted fs-12">{{ $fb->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="ri-star-line fs-2 opacity-50 d-block mb-2"></i>No feedback received yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($feedbacks->hasPages())
        <div class="card-footer">{{ $feedbacks->links() }}</div>
        @endif
    </div>
</x-app-layout>
