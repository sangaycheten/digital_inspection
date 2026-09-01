<x-app-layout>
    <x-slot name="title">Feedback & Rating</x-slot>

    @push('styles')
    <style>
    .star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 4px; }
    .star-rating input { display: none; }
    .star-rating label { font-size: 28px; color: #dee2e6; cursor: pointer; transition: color .15s; line-height: 1; }
    .star-rating label:hover,
    .star-rating label:hover ~ label,
    .star-rating input:checked ~ label { color: #f7b731; }
    .star-rating-sm label { font-size: 18px; }
    </style>
    @endpush

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Feedback & Rating</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Feedback</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible alert-border-left fade show">
        <i class="ri-checkbox-circle-line me-2 align-middle fs-16"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-3">

        {{-- Submit Feedback Form --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-star-line me-2 text-warning"></i>Submit Feedback
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('client.feedback.store') }}">
                        @csrf

                        {{-- Job selector --}}
                        <div class="mb-3">
                            <label class="form-label">Job <span class="text-danger">*</span></label>
                            <select name="job_id" class="form-select @error('job_id') is-invalid @enderror"
                                    {{ $completedJobs->isEmpty() ? 'disabled' : '' }}>
                                <option value="">— Select a completed job —</option>
                                @foreach($completedJobs as $job)
                                <option value="{{ $job->id }}" {{ old('job_id') == $job->id ? 'selected' : '' }}>
                                    {{ $job->site->name ?? $job->site->address }}
                                    · {{ \App\Models\Job::WORK_TYPES[$job->work_type] }}
                                    · {{ $job->updated_at->format('d M Y') }}
                                </option>
                                @endforeach
                            </select>
                            @error('job_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if($completedJobs->isEmpty())
                            <div class="form-text text-muted">No completed jobs available to rate.</div>
                            @endif
                        </div>

                        {{-- Overall rating --}}
                        <div class="mb-4">
                            <label class="form-label fw-medium">Overall Rating <span class="text-danger">*</span></label>
                            <div class="star-rating" id="overallStars">
                                @for($i = 5; $i >= 1; $i--)
                                <input type="radio" name="overall_rating" id="star{{ $i }}" value="{{ $i }}"
                                       {{ old('overall_rating') == $i ? 'checked' : '' }}>
                                <label for="star{{ $i }}"><i class="ri-star-fill"></i></label>
                                @endfor
                            </div>
                            @error('overall_rating')<div class="text-danger fs-12 mt-1">{{ $message }}</div>@enderror
                        </div>

                        {{-- Sub-ratings --}}
                        <p class="text-muted fs-12 fw-semibold text-uppercase mb-2">Rate specific areas (optional)</p>
                        @foreach([
                            'service_quality' => 'Service Quality',
                            'communication'   => 'Communication',
                            'timeliness'      => 'Timeliness',
                            'professionalism' => 'Professionalism',
                        ] as $field => $label)
                        <div class="mb-3">
                            <label class="form-label fs-13">{{ $label }}</label>
                            <div class="star-rating star-rating-sm">
                                @for($i = 5; $i >= 1; $i--)
                                <input type="radio" name="{{ $field }}" id="{{ $field }}_{{ $i }}" value="{{ $i }}"
                                       {{ old($field) == $i ? 'checked' : '' }}>
                                <label for="{{ $field }}_{{ $i }}"><i class="ri-star-fill"></i></label>
                                @endfor
                            </div>
                        </div>
                        @endforeach

                        {{-- Message --}}
                        <div class="mb-4">
                            <label class="form-label">Comments</label>
                            <textarea name="message" rows="4"
                                      class="form-control @error('message') is-invalid @enderror"
                                      placeholder="Share your experience…">{{ old('message') }}</textarea>
                            <div class="form-text text-end" id="charCount">0 / 1000</div>
                            @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-send-plane-line me-1"></i>Submit Feedback
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Past feedback --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-history-line me-2 text-primary"></i>My Previous Feedback
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($feedbacks->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="ri-star-line fs-2 opacity-50 d-block mb-2"></i>
                        You haven't submitted any feedback yet.
                    </div>
                    @else
                    <ul class="list-group list-group-flush">
                        @foreach($feedbacks as $fb)
                        <li class="list-group-item px-3 py-3">
                            <div class="d-flex align-items-start justify-content-between mb-1">
                                <div>
                                    @if($fb->job)
                                    <div class="fw-medium fs-13">
                                        {{ $fb->job->site->name ?? $fb->job->site->address ?? '—' }}
                                        <span class="badge bg-info-subtle text-info ms-1 fs-11">
                                            {{ \App\Models\Job::WORK_TYPES[$fb->job->work_type] }}
                                        </span>
                                    </div>
                                    @else
                                    <div class="fw-medium fs-13 text-muted">General Feedback</div>
                                    @endif
                                    <div class="text-muted fs-12">{{ $fb->created_at->format('d M Y') }}</div>
                                </div>
                                <div class="text-end">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="ri-star-{{ $i <= $fb->overall_rating ? 'fill' : 'line' }} text-warning fs-14"></i>
                                    @endfor
                                </div>
                            </div>

                            @if($fb->service_quality || $fb->communication || $fb->timeliness || $fb->professionalism)
                            <div class="d-flex flex-wrap gap-2 mb-2 mt-1">
                                @foreach([
                                    'service_quality' => 'Quality',
                                    'communication'   => 'Communication',
                                    'timeliness'      => 'Timeliness',
                                    'professionalism' => 'Professionalism',
                                ] as $field => $lbl)
                                @if($fb->$field)
                                <span class="badge bg-light text-dark border fs-11">
                                    {{ $lbl }}:
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="ri-star-{{ $i <= $fb->$field ? 'fill' : 'line' }} text-warning"></i>
                                    @endfor
                                </span>
                                @endif
                                @endforeach
                            </div>
                            @endif

                            @if($fb->message)
                            <p class="fs-13 text-muted mb-0 mt-1">{{ $fb->message }}</p>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    const textarea = document.querySelector('textarea[name="message"]');
    const counter  = document.getElementById('charCount');
    if (textarea && counter) {
        textarea.addEventListener('input', () => {
            counter.textContent = textarea.value.length + ' / 1000';
        });
    }
    </script>
    @endpush
</x-app-layout>
