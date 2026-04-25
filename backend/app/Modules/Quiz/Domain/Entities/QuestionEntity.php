<?php

namespace App\Modules\Quiz\Domain\Entities;

use App\Modules\Quiz\Domain\ValueObjects\QuestionType;

class QuestionEntity
{
    private ?int $id;
    private ?int $quizSessionId;
    private QuestionType $type;
    private string $content;
    private ?string $correctAnswer;
    private ?array $contextData;
    private int $points;

    public function __construct(
        ?int $id,
        ?int $quizSessionId,
        QuestionType $type,
        string $content,
        ?string $correctAnswer,
        ?array $contextData,
        int $points = 10
    ) {
        $this->id = $id;
        $this->quizSessionId = $quizSessionId;
        $this->type = $type;
        $this->content = $content;
        $this->correctAnswer = $correctAnswer;
        $this->contextData = $contextData;
        $this->points = $points;
    }

    public function getId(): ?int { return $this->id; }
    public function getQuizSessionId(): ?int { return $this->quizSessionId; }
    public function getType(): QuestionType { return $this->type; }
    public function getContent(): string { return $this->content; }
    public function getCorrectAnswer(): ?string { return $this->correctAnswer; }
    public function getContextData(): ?array { return $this->contextData; }
    public function getPoints(): int { return $this->points; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'quiz_session_id' => $this->quizSessionId,
            'type' => $this->type->getValue(),
            'content' => $this->content,
            'correct_answer' => $this->correctAnswer,
            'context_data' => $this->contextData,
            'points' => $this->points,
        ];
    }
}
