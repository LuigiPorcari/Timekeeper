<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RaceAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $raceName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $raceName)
    {
        $this->raceName = $raceName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'La tua gara è stata accettata',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.race.accepted',
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

