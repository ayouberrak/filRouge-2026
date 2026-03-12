<?php

namespace App\Modules\User\Application\DTO;

class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password
    ) {}
}