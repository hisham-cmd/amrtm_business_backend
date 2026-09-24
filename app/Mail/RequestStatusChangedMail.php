<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceRequest $serviceRequest,
        public string $statusLabel,
        public ?string $adminNote = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "تحديث طلبك #{$this->serviceRequest->ref_number} - آمر تم",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.request_status_changed',
        );
    }
}
