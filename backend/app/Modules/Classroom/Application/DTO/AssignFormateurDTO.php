<?php

namespace App\Modules\Classroom\Application\DTO;

class AssignFormateurDTO
{
    public function __construct(
        public readonly int $classroom_id,
        public readonly int $formateur_id
    ) {}
}