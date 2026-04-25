<?php

namespace App\Modules\Report\Application\Mails;

use App\Modules\Report\Infrastructure\Models\DailyReportModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyReportSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DailyReportModel $report
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouveau rapport quotidien soumis : ' . $this->report->date,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily_report',
        );
    }
}
