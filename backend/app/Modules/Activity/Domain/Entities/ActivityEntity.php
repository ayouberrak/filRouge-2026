<?php

namespace App\Modules\Activity\Domain\Entities;

use App\Modules\Activity\Domain\ValueObjects\ActivityType;
use JsonSerializable;

class ActivityEntity implements JsonSerializable
{
    private ?int $id;
    private string $title;
    private string $description;
    private ActivityType $type;
    private string $duration;
    private int $points;
    private ?int $formateurId;
    private ?int $classroomId;
    private ?string $scheduledAt;
    private int $durationMinutes;
    private ?string $objectives;
    private ?string $context;
    private ?string $exploration_points;
    private ?string $work_rule;
    private ?string $resources;
    private array $students;

    public function __construct(
        ?int $id,
        string $title,
        string $description,
        ActivityType $type,
        string $duration,
        int $durationMinutes,
        int $points,
        ?int $formateurId,
        ?int $classroomId = null,
        ?string $scheduledAt = null,
        ?string $objectives = null,
        ?string $context = null,
        ?string $exploration_points = null,
        ?string $work_rule = null,
        ?string $resources = null,
        array $students = []
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->duration = $duration;
        $this->durationMinutes = $durationMinutes;
        $this->points = $points;
        $this->formateurId = $formateurId;
        $this->classroomId = $classroomId;
        $this->scheduledAt = $scheduledAt;
        $this->objectives = $objectives;
        $this->context = $context;
        $this->exploration_points = $exploration_points;
        $this->work_rule = $work_rule;
        $this->resources = $resources;
        $this->students = $students;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getType(): ActivityType { return $this->type; }
    public function getDuration(): string { return $this->duration; }
    public function getDurationMinutes(): int { return $this->durationMinutes; }
    public function getPoints(): int { return $this->points; }
    public function getFormateurId(): ?int { return $this->formateurId; }
    public function getClassroomId(): ?int { return $this->classroomId; }
    public function getScheduledAt(): ?string { return $this->scheduledAt; }
    public function getObjectives(): ?string { return $this->objectives; }
    public function getContext(): ?string { return $this->context; }
    public function getExplorationPoints(): ?string { return $this->exploration_points; }
    public function getWorkRule(): ?string { return $this->work_rule; }
    public function getResources(): ?string { return $this->resources; }
    public function getStudents(): array { return $this->students; }

    public function getStatus(): string
    {
        if (!$this->scheduledAt) return 'scheduled';
        
        $scheduled = new \DateTime($this->scheduledAt);
        $now = new \DateTime();
        $end = (clone $scheduled)->modify("+{$this->durationMinutes} minutes");

        if ($now > $end) return 'completed';
        if ($now < $scheduled) return 'scheduled';
        return 'active';
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'activity_type' => $this->type->getValue(),
            'type' => $this->type->getValue(), // Alias for frontend consistency
            'duration' => $this->duration,
            'duration_minutes' => $this->durationMinutes,
            'points' => $this->points,
            'formateur_id' => $this->formateurId,
            'classroom_id' => $this->classroomId,
            'scheduled_at' => $this->scheduledAt,
            'status' => $this->getStatus(),
            'objectives' => $this->objectives,
            'context' => $this->context,
            'exploration_points' => $this->exploration_points,
            'work_rule' => $this->work_rule,
            'resources' => $this->resources,
            'students' => $this->students,
        ];
    }
}