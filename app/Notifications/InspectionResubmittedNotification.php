<?php

namespace App\Notifications;

use App\Models\Job;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InspectionResubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Job $job,
        public readonly User $technician,
        public readonly int $recordCount,
        public readonly array $buildingNames,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $buildings = implode(', ', $this->buildingNames) ?: 'N/A';
        $site      = $this->job->site->name ?? $this->job->site->address ?? 'Unknown Site';
        $client    = $this->job->client->name ?? 'Unknown Client';

        return (new MailMessage)
            ->subject("Inspection Resubmitted – {$client} / {$site}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->technician->name} has resubmitted {$this->recordCount} inspection record(s) after revision.")
            ->line("**Client:** {$client}")
            ->line("**Site:** {$site}")
            ->line("**Building(s):** {$buildings}")
            ->action('Review Inspections', route('reviewer.inspections.index'))
            ->line('Please log in to review the resubmitted inspections.');
    }

    public function toDatabase(object $notifiable): array
    {
        $site   = $this->job->site->name ?? $this->job->site->address ?? 'Unknown Site';
        $client = $this->job->client->name ?? 'Unknown Client';

        return [
            'job_id'       => $this->job->id,
            'technician'   => $this->technician->name,
            'client'       => $client,
            'site'         => $site,
            'buildings'    => $this->buildingNames,
            'record_count' => $this->recordCount,
            'message'      => "{$this->technician->name} resubmitted {$this->recordCount} inspection record(s) for {$client} / {$site}.",
            'url'          => route('reviewer.inspections.index'),
        ];
    }
}
