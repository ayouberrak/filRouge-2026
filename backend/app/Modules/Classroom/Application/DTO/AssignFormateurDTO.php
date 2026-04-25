<?php

namespace App\Modules\Classroom\Application\DTO;

class AssignFormateurDTO
{
    public function __construct(
        public int $classroom_id,
        public int $formateur_id
    ) {}
}