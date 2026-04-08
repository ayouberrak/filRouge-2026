<?php

namespace App\Modules\Activity\Application\DTO;

class CreateActivityDTO
{
    public readonly string $title;
    public readonly string $description;
    public readonly string $type;
    public readonly string $duration;
    public readonly int $durationMinutes;
    public readonly int $points;
    public readonly ?int $formateurId;
    public readonly int $classroomId;
    public readonly ?string $scheduledAt;
    public readonly array $studentIds;
    public readonly ?string $objectives;
    public readonly ?string $context;
    public readonly ?string $exploration_points;
    public readonly ?string $work_rule;
    public readonly ?string $resources;

    public function __construct(
        string $title,
        string $description,
        string $type,
        string $duration,
        int $durationMinutes,
        int $points,
        ?int $formateurId,
        int $classroomId,
        array $studentIds = [],
        ?string $scheduledAt = null,
        ?string $objectives = null,
        ?string $context = null,
        ?string $exploration_points = null,
        ?string $work_rule = null,
        ?string $resources = null
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->duration = $duration;
        $this->durationMinutes = $durationMinutes;
        $this->points = $points;
        $this->formateurId = $formateurId;
        $this->classroomId = $classroomId;
        $this->studentIds = $studentIds;
        $this->scheduledAt = $scheduledAt;
        $this->objectives = $objectives;
        $this->context = $context;
        $this->exploration_points = $exploration_points;
        $this->work_rule = $work_rule;
        $this->resources = $resources;
    }
}
