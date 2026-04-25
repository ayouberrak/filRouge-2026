<?php

namespace App\Modules\User\Application\DTO;

class UpdateUserDTO
{
    public function __construct(
        public ?string $first_name = null,
        public ?string $last_name = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?string $role = null
    ) {}
}
