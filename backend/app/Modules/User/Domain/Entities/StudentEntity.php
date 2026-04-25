<?php

namespace App\Modules\User\Domain\Entities;

class StudentEntity extends UserEntity
{

    private ?int $classroomId;
    private ?int $squadId;

    public function __construct(?int $id, string $firstName, string $lastName, string $email, string $role, string $status, ?int $classroomId = null, ?int $squadId = null) {
        parent::__construct($id, $firstName, $lastName, $email, $role, $status);
        $this->classroomId = $classroomId;
        $this->squadId = $squadId;
    }




    public function getClassroomId(): ?int 
    {
         return $this->classroomId; 
    }

    public function getSquadId(): ?int 
    {
         return $this->squadId; 
    }
}