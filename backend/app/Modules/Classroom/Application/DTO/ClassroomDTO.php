<?php

namespace App\Modules\Classroom\Application\DTO;

class ClassroomDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?int $formateur_id = null
    ) {}
}
