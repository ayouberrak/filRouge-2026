<?php

namespace App\Modules\Report\Domain\Entities;

class DailyReportEntity
{
    private ?int $id;
    private string $date;
    private int $absencesCount;
    private string $briefStatus;

    public function __construct(
        ?int $id,
        string $date,
        int $absencesCount,
        string $briefStatus
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->absencesCount = $absencesCount;
        $this->briefStatus = $briefStatus;
    }

    public function getId(): ?int { return $this->id; }
    public function getDate(): string { return $this->date; }
    public function getAbsencesCount(): int { return $this->absencesCount; }
    public function getBriefStatus(): string { return $this->briefStatus; }
}