<?php

namespace App\Modules\User\Domain\Entities;

class StudentEntity extends UserEntity
{
    private int $points;

    public function __construct(
        ?int $id,
        string $firstName,
        string $lastName,
        string $email,
        string $role,
        string $status,
        int $points = 0
    ) {
        parent::__construct($id, $firstName, $lastName, $email, $role, $status);
        $this->points = $points;
    }

    public function getPoints(): int { return $this->points; }
}