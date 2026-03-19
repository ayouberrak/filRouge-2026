<?php

namespace App\Modules\Report\Domain\Entities;

class DailyReportEntity
{
    private ?int $id;
    private int $formateurId;
    private int $classroomId;
    private string $date;
    private int $absencesCount;
    private string $briefStatus;
    private ?string $note;

    public function __construct(
        ?int $id,
        int $formateurId,
        int $classroomId,
        string $date,
        int $absencesCount,
        string $briefStatus,
        ?string $note = null
    ) {
        $this->id = $id;
        $this->formateurId = $formateurId;
        $this->classroomId = $classroomId;
        $this->date = $date;
        $this->absencesCount = $absencesCount;
        $this->briefStatus = $briefStatus;
        $this->note = $note;
    }

    public function getId(): ?int { return $this->id; }
    public function getFormateurId(): int { return $this->formateurId; }
    public function getClassroomId(): int { return $this->classroomId; }
    public function getDate(): string { return $this->date; }
    public function getAbsencesCount(): int { return $this->absencesCount; }
    public function getBriefStatus(): string { return $this->briefStatus; }
    public function getNote(): ?string { return $this->note; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'formateur_id' => $this->formateurId,
            'classroom_id' => $this->classroomId,
            'date' => $this->date,
            'absences_count' => $this->absencesCount,
            'brief_status' => $this->briefStatus,
            'note' => $this->note,
        ];
    }
}