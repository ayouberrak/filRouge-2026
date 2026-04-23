<?php

namespace App\Modules\Activity\Application\DTO;

class CreateActivityDTO
{
    public readonly string $title;
    public readonly string $description;
    public readonly string $type;
    public readonly string $duration;
    public readonly int $durationMinutes;
    public readonly ?int $formateurId;
    public readonly int $classroomId;
    public readonly ?string $scheduledAt;
    public readonly array $studentIds;

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
