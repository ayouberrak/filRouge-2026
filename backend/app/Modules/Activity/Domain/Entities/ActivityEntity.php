<?php

namespace App\Modules\Activity\Domain\Entities;

use App\Modules\Activity\Domain\ValueObjects\ActivityType;

class ActivityEntity
{
    private ?int $id;
    private string $title;
    private string $description;
    private ActivityType $type;
    private int $duration;
    private int $points;
    private ?int $formateurId;
    private ?int $classroomId;

    public function __construct(
        ?int $id,
        string $title,
        string $description,
        ActivityType $type,
        int $duration,
        int $points,
        ?int $formateurId,
        ?int $classroomId = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->duration = $duration;
        $this->points = $points;
        $this->formateurId = $formateurId;
        $this->classroomId = $classroomId;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getType(): ActivityType { return $this->type; }
    public function getDuration(): int { return $this->duration; }
    public function getPoints(): int { return $this->points; }
    public function getFormateurId(): ?int { return $this->formateurId; }
    public function getClassroomId(): ?int { return $this->classroomId; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'activity_type' => $this->type->getValue(),
            'duration' => $this->duration,
            'points' => $this->points,
            'formateur_id' => $this->formateurId,
            'classroom_id' => $this->classroomId,
        ];
    }
}