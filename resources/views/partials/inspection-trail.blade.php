{{--
    Inspection Assessment Trail
    Required: $activityLog (Collection of Spatie Activity records, ordered by created_at)
--}}
@php
$trail = [];
foreach ($activityLog as $log) {
    $attrs = $log->properties['attributes'] ?? [];
    $old   = $log->properties['old'] ?? [];

    if ($log->description === 'created') {
        $trail[] = [
            'label' => 'Saved as Draft',
            'icon'  => 'ri-save-3-line',
            'color' => 'warning',
            'by'    => $log->causer?->name ?? 'System',
            'at'    => $log->created_at,
            'note'  => null,
        ];
        continue;
    }

    if ($log->description !== 'updated') continue;

    $newStatus = $attrs['document_status'] ?? null;
    $oldStatus = $old['document_status'] ?? null;

    if (!$newStatus || $newStatus === $oldStatus) continue;

    if ($newStatus === 'submitted') {
        $alreadySubmitted = collect($trail)->contains('label', 'Submitted for Review') ||
                            collect($trail)->contains('label', 'Resubmitted for Review');
        $trail[] = [
            'label' => $alreadySubmitted ? 'Resubmitted for Review' : 'Submitted for Review',
            'icon'  => 'ri-send-plane-line',
            'color' => 'primary',
            'by'    => $log->causer?->name ?? 'System',
            'at'    => $log->created_at,
            'note'  => null,
        ];
    } elseif ($newStatus === 'draft' && $oldStatus === 'submitted') {
        $note = $attrs['required_action'] ?? null;
        if ($note) $note = ltrim(str_replace('[REJECTED]', '', $note));
        $trail[] = [
            'label' => 'Sent Back for Revision',
            'icon'  => 'ri-arrow-go-back-line',
            'color' => 'danger',
            'by'    => $log->causer?->name ?? 'System',
            'at'    => $log->created_at,
            'note'  => $note ?: null,
        ];
    } elseif ($newStatus === 'approved') {
        $trail[] = [
            'label' => 'Approved',
            'icon'  => 'ri-shield-check-line',
            'color' => 'success',
            'by'    => $log->causer?->name ?? 'System',
            'at'    => $log->created_at,
            'note'  => null,
        ];
    }
}
@endphp

@if(count($trail) > 0)
<div class="card mb-3">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="ri-history-line me-2 text-primary"></i>Assessment Trail
        </h6>
    </div>
    <div class="card-body">
        <div style="position:relative; padding-left:40px">
            @foreach($trail as $step)
            <div style="position:relative; @if(!$loop->last) padding-bottom:24px @endif">

                {{-- dot --}}
                <div class="bg-{{ $step['color'] }}-subtle text-{{ $step['color'] }}"
                     style="position:absolute;left:-40px;width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;z-index:1">
                    <i class="{{ $step['icon'] }}" style="font-size:13px"></i>
                </div>

                {{-- connecting line --}}
                @if(!$loop->last)
                <div style="position:absolute;left:-29px;top:24px;bottom:0;width:2px;background:#dee2e6"></div>
                @endif

                {{-- content --}}
                <div style="padding-top:2px">
                    <p class="mb-0 fw-semibold fs-13 text-{{ $step['color'] }}">{{ $step['label'] }}</p>
                    <p class="text-muted fs-12 mb-0">
                        {{ $step['at']->format('d-m-Y H:i:s') }} &bull; {{ $step['by'] }}
                    </p>
                    @if($step['note'])
                    <div class="mt-1 p-2 rounded bg-light border-start border-2 border-{{ $step['color'] }} fs-12 text-muted">
                        {{ $step['note'] }}
                    </div>
                    @endif
                </div>

            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
