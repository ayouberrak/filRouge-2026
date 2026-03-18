<?php

namespace App\Modules\User\Application\DTO;

class UserDTO
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $role,
        public string $status,
        public ?string $speciality = null,
        public int $points = 0,
        public ?int $classroom_id = null,
        public ?int $squad_id = null
    ) {}
}
