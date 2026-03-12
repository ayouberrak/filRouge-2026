<?php

namespace App\Modules\Deliverable\Application\DTO;

class DeliverableDTO
{
    public function __construct(
        public readonly string $link,
        public readonly string $date_submission,
        public readonly string $status,
        public readonly int $student_id,
        public readonly int $brief_id
    ) {}
}
