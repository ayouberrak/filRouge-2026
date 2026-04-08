<?php

namespace App\Modules\Absence\Domain\Entities;

use App\Modules\Absence\Domain\ValueObjects\AbsenceStatus;
use InvalidArgumentException;

class AbsenceEntity
{
    public function __construct(
        private ?int $id,
        private int $studentId, // New required property based on DB structure
        private string $date,
        private int $duration, // renamed duree to duration for consistency
        private AbsenceStatus $status,
        private ?string $justificationFile = null,
        private ?string $studentName = null,
        private ?string $classroomName = null
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudentId(): int
    {
        return $this->studentId;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getStatus(): AbsenceStatus
    {
        return $this->status;
    }

    public function getJustificationFile(): ?string
    {
        return $this->justificationFile;
    }

    public function getStudentName(): ?string
    {
        return $this->studentName;
    }

    public function getClassroomName(): ?string
    {
        return $this->classroomName;
    }

    // Behavioral Methods

    public function submitJustification(string $fileName): void
    {
        if ($this->status->getValue() !== AbsenceStatus::PENDING) {
            throw new InvalidArgumentException("Can only submit justification for pending absences.");
        }
        
        $this->justificationFile = $fileName;
    }

    public function approve(): void
    {
        if ($this->justificationFile === null) {
            throw new InvalidArgumentException("Cannot approve an absence without a justification file.");
        }

        $this->status = new AbsenceStatus(AbsenceStatus::JUSTIFIED);
    }

    public function reject(): void
    {
        if ($this->justificationFile === null) {
            throw new InvalidArgumentException("Cannot reject an absence without a justification file.");
        }

        $this->status = new AbsenceStatus(AbsenceStatus::REJECTED);
    }
}