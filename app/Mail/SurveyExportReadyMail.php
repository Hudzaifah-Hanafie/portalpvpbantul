<?php

namespace App\Mail;

use App\Models\Survey;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SurveyExportReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Survey $survey,
        public string $filePath
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hasil Ekspor Survei: ' . $this->survey->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.survey.export-ready',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('local', $this->filePath)
                ->as('survey-' . $this->survey->slug . '-responses.xlsx')
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
