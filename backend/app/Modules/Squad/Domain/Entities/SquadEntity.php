<?php

namespace App\Modules\Squad\Domain\Entities;

use App\Modules\Squad\Domain\ValueObjects\SquadName;

class SquadEntity
{
    public function __construct(
        private ?int $id,
        private SquadName $name,
        private int $classroomId,
        private array $members = []
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): SquadName
    {
        return $this->name;
    }

    public function getClassroomId(): int
    {
        return $this->classroomId;
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    // Behavioral Methods

    public function rename(string $newName): void
    {
        $this->name = new SquadName($newName);
    }

    public function addMember(int $userId): void
    {
        if (!in_array($userId, $this->members)) {
            $this->members[] = $userId;
        }
    }

    public function removeMember(int $userId): void
    {
        $index = array_search($userId, $this->members);
        if ($index !== false) {
            unset($this->members[$index]);
            $this->members = array_values($this->members);
        }
    }
}