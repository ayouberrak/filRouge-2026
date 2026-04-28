<?php

namespace App\Modules\Absence\Application\DTO;

class AbsenceDTO
{
    public function __construct(
        public int $student_id,
        public string $date,
        public int $duration,
        public string $status,
        public ?string $justification_file = null
    ) {}
}
