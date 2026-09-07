<?php

namespace App\Mail;

use App\Models\Job;
use App\Models\InspectionRecord;
use App\Models\MasterLookup;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InspectionCertificateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Job $job) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Inspection Certificate – ' . ($this->job->site->name ?? $this->job->site->address ?? 'Inspection'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.inspection-certificate',
            with: ['job' => $this->job],
        );
    }

    public function attachments(): array
    {
        $job = $this->job;

        $records = InspectionRecord::with(['asset.building'])
            ->where('job_id', $job->id)
            ->where('document_status', 'approved')
            ->get()
            ->groupBy('asset_id')
            ->map(fn ($recs) => $recs->sortByDesc('created_at')->first())
            ->values();

        $assetTypes = MasterLookup::assetTypeMap();

        $pdf = Pdf::loadView('admin.jobs.certificate', compact('job', 'records', 'assetTypes'))
            ->setPaper('a4', 'portrait');

        $filename = 'inspection-certificate-' . strtoupper(substr($job->id, -8)) . '.pdf';

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
