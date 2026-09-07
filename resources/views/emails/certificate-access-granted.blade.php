@component('mail::message')
# Inspection Report Available

Hello,

Your inspection report for **{{ $job->site->name ?? $job->site->address }}** is now available for download.

| | |
|---|---|
| **Certificate No.** | CERT-{{ strtoupper(substr($job->id, 0, 8)) }} |
| **Work Type** | {{ \App\Models\Job::WORK_TYPES[$job->work_type] ?? $job->work_type }} |
| **Site** | {{ $job->site->name ?? $job->site->address }} |

@component('mail::button', ['url' => route('client.reports.index')])
View Inspection Reports
@endcomponent

Thanks,
{{ config('app.name') }}
@endcomponent
