<?php

namespace App\Modules\Quiz\Application\DTO;

class CreateQuizSessionDTO
{
    public function __construct(
        private int $briefId,
        private int $formateurId,
        private int $timerMinutes,
        private int $passingScore,
        private array $questions
    ) {}

    public function getBriefId(): int { return $this->briefId; }
    public function getFormateurId(): int { return $this->formateurId; }
    public function getTimerMinutes(): int { return $this->timerMinutes; }
    public function getPassingScore(): int { return $this->passingScore; }
    public function getQuestions(): array { return $this->questions; }
}
