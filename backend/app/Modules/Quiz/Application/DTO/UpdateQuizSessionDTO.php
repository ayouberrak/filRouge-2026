<?php

namespace App\Modules\Quiz\Application\DTO;

class UpdateQuizSessionDTO
{
    public function __construct(
        private int $sessionId,
        private string $title,
        private ?string $description,
        private int $classroomId,
        private int $timerMinutes,
        private int $passingScore,
        private array $questions
    ) {}

    public function getSessionId(): int { return $this->sessionId; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getClassroomId(): int { return $this->classroomId; }
    public function getTimerMinutes(): int { return $this->timerMinutes; }
    public function getPassingScore(): int { return $this->passingScore; }
    public function getQuestions(): array { return $this->questions; }
}
