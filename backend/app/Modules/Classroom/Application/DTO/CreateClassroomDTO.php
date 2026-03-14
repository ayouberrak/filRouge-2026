<?php

namespace App\Modules\Classroom\Application\DTO;

class CreateClassroomDTO
{
    public function __construct(
        public  string $name,
        public ?int $formateur_id = null
    ) {}
}
