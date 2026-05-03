<?php

namespace App\Modules\Quiz\Domain\Repositories;

use App\Modules\Quiz\Domain\Entities\QuizSessionEntity;
use App\Modules\Quiz\Domain\Entities\ResponseEntity;

interface QuizRepositoryInterface
{
    public function saveSession(QuizSessionEntity $session): QuizSessionEntity;
    public function findSessionById(int $id): ?QuizSessionEntity;
    public function saveResponse(ResponseEntity $response): ResponseEntity;
    public function findResponsesBySessionId(int $sessionId): array;
    public function findResponseByQuestionAndStudent(int $questionId, int $studentId): ?ResponseEntity;
    public function deleteQuestionsBySessionId(int $sessionId): void;
}
