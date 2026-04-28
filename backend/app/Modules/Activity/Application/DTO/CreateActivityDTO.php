<?php

namespace App\Modules\Activity\Application\DTO;

class CreateActivityDTO
{
    public string $title;
    public string $description;
    public string $type;
    public string $duration;
    public int $durationMinutes;
    public ?int $formateurId;
    public int $classroomId;
    public ?string $scheduledAt;
    public array $studentIds;

    public function __construct(
        string $title,
        string $description,
        string $type,
        string $duration,
        int $durationMinutes,
        ?int $formateurId,
        int $classroomId,
        array $studentIds = [],
        ?string $scheduledAt = null
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->duration = $duration;
        $this->durationMinutes = $durationMinutes;
        $this->formateurId = $formateurId;
        $this->classroomId = $classroomId;
        $this->studentIds = $studentIds;
        $this->scheduledAt = $scheduledAt;
    }
}
