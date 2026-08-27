<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Job $job,
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
        $workType  = \App\Models\Job::WORK_TYPES[$this->job->work_type] ?? $this->job->work_type;
        $scheduled = $this->job->scheduled_date?->format('d M Y') ?? 'Not set';

        return (new MailMessage)
            ->subject("You have been assigned to a job – {$client} / {$site}")
            ->greeting("Hello {$notifiable->name},")
            ->line("You have been assigned to a {$workType} job.")
            ->line("**Client:** {$client}")
            ->line("**Site:** {$site}")
            ->line("**Building(s):** {$buildings}")
            ->line("**Scheduled Date:** {$scheduled}")
            ->action('View Job', route('technician.jobs.show', $this->job))
            ->line('Please log in to view your job details and begin inspection when ready.');
    }

    public function toDatabase(object $notifiable): array
    {
        $site     = $this->job->site->name ?? $this->job->site->address ?? 'Unknown Site';
        $client   = $this->job->client->name ?? 'Unknown Client';
        $workType = \App\Models\Job::WORK_TYPES[$this->job->work_type] ?? $this->job->work_type;

        return [
            'job_id'      => $this->job->id,
            'client'      => $client,
            'site'        => $site,
            'work_type'   => $workType,
            'buildings'   => $this->buildingNames,
            'scheduled'   => $this->job->scheduled_date?->format('d M Y'),
            'message'     => "You have been assigned to a {$workType} job for {$client} / {$site}.",
            'url'         => route('technician.jobs.show', $this->job),
        ];
    }
}
