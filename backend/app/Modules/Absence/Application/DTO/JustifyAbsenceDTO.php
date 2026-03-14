<?php

namespace App\Modules\Absence\Application\DTO;

class JustifyAbsenceDTO
{
    public function __construct(
        public int $absence_id,
        public string $justification_file // Could be a path or filename after upload
    ) {}
}
