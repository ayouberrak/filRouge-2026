<?php

namespace App\Modules\User\Domain\Entities;

class FormateurEntity extends UserEntity
{
    private ?string $speciality;

    public function __construct(
        ?int $id,
        string $firstName,
        string $lastName,
        string $email,
        string $role,
        string $status,
        ?string $speciality
    ) {
        parent::__construct($id, $firstName, $lastName, $email, $role, $status);
        $this->speciality = $speciality;
    }

    public function getSpeciality(): ?string { return $this->speciality; }
}