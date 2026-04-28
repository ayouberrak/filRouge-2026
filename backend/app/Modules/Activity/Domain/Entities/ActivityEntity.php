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
    private ?int $formateurId; 
    private ?int $classroomId; 
    private ?string $scheduledAt;
    private int $durationMinutes; 
    private array $students; 

    public function __construct(
        ?int $id,
        string $title,
        string $description,
        ActivityType $type,
        string $duration,
        int $durationMinutes,
        ?int $formateurId,
        ?int $classroomId = null,
        ?string $scheduledAt = null,
        array $students = []
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->duration = $duration;
        $this->durationMinutes = $durationMinutes;
        $this->formateurId = $formateurId;
        $this->classroomId = $classroomId;
        $this->scheduledAt = $scheduledAt;
        $this->students = $students;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getType(): ActivityType { return $this->type; }
    public function getDuration(): string { return $this->duration; }
    public function getDurationMinutes(): int { return $this->durationMinutes; }
    public function getFormateurId(): ?int { return $this->formateurId; }
    public function getClassroomId(): ?int { return $this->classroomId; }
    public function getScheduledAt(): ?string { return $this->scheduledAt; }
    public function getStudents(): array { return $this->students; }

    public function getStatus(): string
    {
        if (!$this->scheduledAt){
            return 'scheduled';
        }
        
        $scheduled = new \DateTime($this->scheduledAt);
        $now = new \DateTime();
        $end = (clone $scheduled)->modify("+{$this->durationMinutes} minutes");

        if ($now > $end) {
            return 'completed';
        }
        if ($now < $scheduled) {
            return 'scheduled';
        }
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
            'type' => $this->type->getValue(), 
            'duration' => $this->duration,
            'duration_minutes' => $this->durationMinutes,
            'formateur_id' => $this->formateurId,
            'classroom_id' => $this->classroomId,
            'scheduled_at' => $this->scheduledAt,
            'status' => $this->getStatus(),
            'students' => $this->students,
        ];
    }
}