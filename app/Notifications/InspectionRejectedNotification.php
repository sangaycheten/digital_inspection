<?php

namespace App\Notifications;

use App\Models\InspectionRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InspectionRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly InspectionRecord $inspection,
        public readonly string $rejectionNote,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $assetCode = $this->inspection->asset->asset_code ?? 'Unknown Asset';
        $site      = $this->inspection->asset->site->name ?? $this->inspection->asset->site->address ?? 'Unknown Site';
        $client    = $this->inspection->asset->site->client->name ?? 'Unknown Client';
        $job       = $this->inspection->job;

        return (new MailMessage)
            ->subject("Inspection Sent Back for Revision – {$assetCode}")
            ->greeting("Hello {$notifiable->name},")
            ->line("An inspection record you submitted has been sent back for revision.")
            ->line("**Asset:** {$assetCode}")
            ->line("**Client:** {$client}")
            ->line("**Site:** {$site}")
            ->line("**Reason:** {$this->rejectionNote}")
            ->action('View Job', route('technician.jobs.inspect', $job))
            ->line('Please review the feedback and resubmit the corrected inspection.');
    }

    public function toDatabase(object $notifiable): array
    {
        $assetCode = $this->inspection->asset->asset_code ?? 'Unknown Asset';
        $site      = $this->inspection->asset->site->name ?? $this->inspection->asset->site->address ?? 'Unknown Site';
        $client    = $this->inspection->asset->site->client->name ?? 'Unknown Client';
        $job       = $this->inspection->job;

        return [
            'inspection_id'  => $this->inspection->id,
            'job_id'         => $job?->id,
            'asset_code'     => $assetCode,
            'client'         => $client,
            'site'           => $site,
            'rejection_note' => $this->rejectionNote,
            'message'        => "Inspection for {$assetCode} ({$client} / {$site}) was sent back for revision: {$this->rejectionNote}",
            'url'            => $job ? route('technician.jobs.inspect', $job) : null,
        ];
    }
}
