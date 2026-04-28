<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $code,
        public readonly string $userName,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Код подтверждения - Крепкая Башня',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.verification-code',
        );
    }
}
