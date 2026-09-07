<?php

namespace App\Mail;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CertificateAccessGrantedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Job $job) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Inspection Report Available – ' . ($this->job->site->name ?? $this->job->site->address ?? 'Your Site'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.certificate-access-granted', with: ['job' => $this->job]);
    }
}
