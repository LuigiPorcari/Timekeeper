<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RaceRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $raceName;

    public function __construct(string $raceName)
    {
        $this->raceName = $raceName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'La tua gara è stata rifiutata',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.race.rejected',
            with: [
                'raceName' => $this->raceName,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
