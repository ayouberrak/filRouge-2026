<?php

namespace App\Modules\Brief\Application\Mails;

use App\Modules\Brief\Infrastructure\Models\BriefModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BriefAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BriefModel $brief
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouveau Brief : ' . $this->brief->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.brief_assigned',
        );
    }
}
