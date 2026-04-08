<?php

namespace App\Modules\User\Domain\Entities;

class StudentEntity extends UserEntity
{
    private int $points;
    private ?int $classroomId;
    private ?int $squadId;

    public function __construct(
        ?int $id,
        string $firstName,
        string $lastName,
        string $email,
        string $role,
        string $status,
        int $points = 0,
        ?int $classroomId = null,
        ?int $squadId = null
    ) {
        parent::__construct($id, $firstName, $lastName, $email, $role, $status);
        $this->points = $points;
        $this->classroomId = $classroomId;
        $this->squadId = $squadId;
    }

    public function getPoints(): int { return $this->points; }
    public function getClassroomId(): ?int { return $this->classroomId; }
    public function getSquadId(): ?int { return $this->squadId; }
}