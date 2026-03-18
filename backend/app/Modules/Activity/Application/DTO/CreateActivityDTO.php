<?php

namespace App\Modules\Activity\Application\DTO;

class CreateActivityDTO
{
    public readonly string $title;
    public readonly string $description;
    public readonly string $type;
    public readonly int $duration;
    public readonly int $points;
    public readonly ?int $formateurId;
    public readonly int $classroomId;
    public readonly array $studentIds;

    public function __construct(
        string $title,
        string $description,
        string $type,
        int $duration,
        int $points,
        ?int $formateurId,
        int $classroomId,
        array $studentIds = []
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->duration = $duration;
        $this->points = $points;
        $this->formateurId = $formateurId;
        $this->classroomId = $classroomId;
        $this->studentIds = $studentIds;
    }
}
