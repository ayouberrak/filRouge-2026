<?php

namespace App\Modules\Report\Application\DTO;

class DailyReportDTO
{
    public readonly int $classroomId;
    public readonly string $date;
    public readonly int $absencesCount;
    public readonly string $briefStatus;
    public readonly ?string $technicalTopics;
    public readonly ?string $workshopsDone;
    public readonly int $classMood;
    public readonly bool $objectivesMet;
    public readonly int $formateurId;
    public readonly ?string $note;

    public function __construct(
        int $classroomId,
        string $date,
        int $absencesCount,
        string $briefStatus,
        int $formateurId,
        ?string $technicalTopics = null,
        ?string $workshopsDone = null,
        int $classMood = 3,
        bool $objectivesMet = true,
        ?string $note = null
    ) {
        $this->classroomId = $classroomId;
        $this->date = $date;
        $this->absencesCount = $absencesCount;
        $this->briefStatus = $briefStatus;
        $this->formateurId = $formateurId;
        $this->technicalTopics = $technicalTopics;
        $this->workshopsDone = $workshopsDone;
        $this->classMood = $classMood;
        $this->objectivesMet = $objectivesMet;
        $this->note = $note;
    }
}
