<?php

namespace App\Modules\Classroom\Domain\Entities;

use App\Modules\Classroom\Domain\ValueObjects\ClassroomName;

class ClassroomEntity
{
    private ?int $id;
    private ClassroomName $name;
    private ?int $formateurId;

    public function __construct(?int $id, ClassroomName $name, ?int $formateurId = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->formateurId = $formateurId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ClassroomName
    {
        return $this->name;
    }

    public function getFormateurId(): ?int
    {
        return $this->formateurId;
    }

    public function assignFormateur(int $formateurId): void
    {
        $this->formateurId = $formateurId;
    }
}