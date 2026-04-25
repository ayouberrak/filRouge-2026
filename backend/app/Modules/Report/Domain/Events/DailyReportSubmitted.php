<?php

namespace App\Modules\Report\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DailyReportSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $reportId
    ) {}
}
