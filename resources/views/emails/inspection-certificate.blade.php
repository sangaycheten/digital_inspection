<x-mail::message>
# Inspection Certificate

Dear {{ $job->client->name ?? 'Valued Client' }},

Please find attached the **Inspection Certificate** for the following job:

<x-mail::table>
| Field | Details |
|-------|---------|
| Site | {{ $job->site->name ?? $job->site->address ?? '—' }} |
| Work Type | {{ \App\Models\Job::WORK_TYPES[$job->work_type] }} |
| Scheduled Date | {{ $job->scheduled_date?->format('d M Y') ?? '—' }} |
| Certificate No | CERT-{{ strtoupper(substr($job->id, 0, 8)) }} |
| Issued On | {{ now()->format('d M Y') }} |
</x-mail::table>

The certificate PDF is attached to this email. Please retain it for your records as proof of compliance.

If you have any questions or require further information, please do not hesitate to contact us.

Thanks,<br>
**{{ config('app.name') }}** — APlus Safety
</x-mail::message>
