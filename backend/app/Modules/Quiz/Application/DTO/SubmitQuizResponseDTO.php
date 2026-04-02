<?php

namespace App\Modules\Quiz\Application\DTO;

class SubmitQuizResponseDTO
{
    public function __construct(
        private int $questionId,
        private int $studentId,
        private string $responseText
    ) {}

    public function getQuestionId(): int { return $this->questionId; }
    public function getStudentId(): int { return $this->studentId; }
    public function getResponseText(): string { return $this->responseText; }
}
