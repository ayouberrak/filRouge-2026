<?php

namespace App\Modules\Quiz\Application\DTO;

class CreateQuizSessionDTO
{
    public function __construct(
        private int $formateurId,
        private string $title,
        private ?string $description,
        private int $classroomId,
        private int $timerMinutes,
        private int $passingScore,
        private array $questions
    ) {}

    public function getFormateurId(): int { return $this->formateurId; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getClassroomId(): int { return $this->classroomId; }
    public function getTimerMinutes(): int { return $this->timerMinutes; }
    public function getPassingScore(): int { return $this->passingScore; }
    public function getQuestions(): array { return $this->questions; }
}
