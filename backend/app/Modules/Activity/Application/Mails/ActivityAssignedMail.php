<?php

namespace App\Modules\Activity\Application\Mails;

use App\Modules\Activity\Infrastructure\Models\ActivityModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivityAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ActivityModel $activity
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle activité assignée : ' . $this->activity->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.activity_assigned',
        );
    }
}
