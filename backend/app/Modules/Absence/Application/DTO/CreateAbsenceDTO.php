<?php

namespace App\Modules\Absence\Application\DTO;

class CreateAbsenceDTO
{
    public function __construct(
        public int $student_id,
        public string $date,
        public int $duration
    ) {}
}
