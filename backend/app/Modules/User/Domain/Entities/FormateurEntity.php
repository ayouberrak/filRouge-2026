<?php

namespace App\Modules\User\Domain\Entities;

class FormateurEntity extends UserEntity
{
    public function __construct(
        ?int $id,
        string $firstName,
        string $lastName,
        string $email,
        string $role,
        string $status
    ) {
        parent::__construct($id, $firstName, $lastName, $email, $role, $status);
    }
}