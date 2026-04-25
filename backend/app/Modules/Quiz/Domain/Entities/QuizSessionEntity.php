<?php

namespace App\Modules\Quiz\Domain\Entities;

use App\Modules\Quiz\Domain\ValueObjects\QuizStatus;
use DateTime;

class QuizSessionEntity
{
    private ?int $id;
    private int $briefId;
    private int $formateurId;
    private QuizStatus $status;
    private int $timerMinutes;
    private int $passingScore;
    
    /** @var QuestionEntity[] */
    private array $questions;

    public function __construct(
        ?int $id,
        int $briefId,
        int $formateurId,
        QuizStatus $status,
        int $timerMinutes,
        int $passingScore = 75,
        array $questions = []
    ) {
        $this->id = $id;
        $this->briefId = $briefId;
        $this->formateurId = $formateurId;
        $this->status = $status;
        $this->timerMinutes = $timerMinutes;
        $this->passingScore = $passingScore;
        $this->questions = $questions;
    }

    public function getId(): ?int { return $this->id; }
    public function getBriefId(): int { return $this->briefId; }
    public function getFormateurId(): int { return $this->formateurId; }
    public function getStatus(): QuizStatus { return $this->status; }
    public function getTimerMinutes(): int { return $this->timerMinutes; }
    public function getPassingScore(): int { return $this->passingScore; }
    
    /** @return QuestionEntity[] */
    public function getQuestions(): array { return $this->questions; }

    public function isCompleted(): bool
    {
        return $this->status->isCompleted();
    }
}
