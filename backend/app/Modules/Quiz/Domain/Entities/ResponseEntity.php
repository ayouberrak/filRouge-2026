<?php

namespace App\Modules\Quiz\Domain\Entities;

class ResponseEntity
{
    private ?int $id;
    private int $questionId;
    private int $studentId;
    private string $responseText;
    private float $score;
    private bool $isCorrect;
    private ?string $aiFeedback;

    public function __construct(
        ?int $id,
        int $questionId,
        int $studentId,
        string $responseText,
        float $score = 0,
        bool $isCorrect = false,
        ?string $aiFeedback = null
    ) {
        $this->id = $id;
        $this->questionId = $questionId;
        $this->studentId = $studentId;
        $this->responseText = $responseText;
        $this->score = $score;
        $this->isCorrect = $isCorrect;
        $this->aiFeedback = $aiFeedback;
    }

    public function getId(): ?int { return $this->id; }
    public function getQuestionId(): int { return $this->questionId; }
    public function getStudentId(): int { return $this->studentId; }
    public function getResponseText(): string { return $this->responseText; }
    public function getScore(): float { return $this->score; }
    public function isCorrect(): bool { return $this->isCorrect; }
    public function getAiFeedback(): ?string { return $this->aiFeedback; }
}
