<?php

namespace App\Modules\User\Domain\Entities;

abstract class UserEntity
{
    protected ?int $id;
    protected string $firstName;
    protected string $lastName;
    protected string $email;
    protected string $role;
    protected string $status;

    public function __construct(?int $id, string $firstName, string $lastName, string $email, string $role, string $status) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->role = $role;
        $this->status = $status;
    }

    public function getId(): ?int { 
        return $this->id; 
    }
    public function getFirstName(): string { 
        return $this->firstName; 
    }
    public function getLastName(): string { 
        return $this->lastName; 
    }
    public function getEmail(): string { 
        return $this->email; 
    }
    public function getRole(): string { 
        return $this->role; 
    }
    public function getStatus(): string { 
        return $this->status; 
    }
}