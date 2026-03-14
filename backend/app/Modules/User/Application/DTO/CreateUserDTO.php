<?php

namespace App\Modules\User\Application\DTO;

class CreateUserDTO
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $password,
        public string $role,
        public ?string $speciality = null
    ) {}
}
