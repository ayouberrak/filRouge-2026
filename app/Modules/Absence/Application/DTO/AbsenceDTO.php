<?php

namespace App\Modules\Absence\Application\DTO;

class AbsenceDTO
{
    public function __construct(
        public readonly int $student_id,
        public readonly string $date,
        public readonly int $duration,
        public readonly string $status,
        public readonly ?string $justification_file = null
    ) {}
}
