<?php

namespace App\Modules\Report\Application\DTO;

class DailyReportDTO
{
    public function __construct(
        public readonly int $user_id,
        public readonly string $date,
        public readonly int $absences_count,
        public readonly string $brief_status
    ) {}
}
