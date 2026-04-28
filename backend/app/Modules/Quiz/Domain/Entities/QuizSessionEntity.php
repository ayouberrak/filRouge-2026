<?php

namespace App\Modules\Quiz\Domain\Entities;

use App\Modules\Quiz\Domain\ValueObjects\QuizStatus;

class QuizSessionEntity
{
    private ?int $id;
    private int $formateurId;
    private ?string $title;
    private ?string $description;
    private ?int $classroomId;
    private QuizStatus $status;
    private int $timerMinutes;
    private int $passingScore;
    
    private array $questions;

    public function __construct(
        ?int $id,
        int $formateurId,
        ?string $title,
        ?string $description,
        ?int $classroomId,
        QuizStatus $status,
        int $timerMinutes,
        int $passingScore = 75,
        array $questions = []
    ) {
        $this->id = $id;
        $this->formateurId = $formateurId;
        $this->title = $title;
        $this->description = $description;
        $this->classroomId = $classroomId;
        $this->status = $status;
        $this->timerMinutes = $timerMinutes;
        $this->passingScore = $passingScore;
        $this->questions = $questions;
    }

    public function getId(): ?int { return $this->id; }
    public function getFormateurId(): int { return $this->formateurId; }
    public function getTitle(): ?string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getClassroomId(): ?int { return $this->classroomId; }
    public function getStatus(): QuizStatus { return $this->status; }
    public function getTimerMinutes(): int { return $this->timerMinutes; }
    public function getPassingScore(): int { return $this->passingScore; }
    
    public function getQuestions(): array { return $this->questions; }

    public function isCompleted(): bool
    {
        return $this->status->isCompleted();
    }
}
