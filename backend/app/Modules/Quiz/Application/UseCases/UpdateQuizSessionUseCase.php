<?php

namespace App\Modules\Quiz\Application\UseCases;

use App\Modules\Quiz\Application\DTO\UpdateQuizSessionDTO;
use App\Modules\Quiz\Domain\Entities\QuestionEntity;
use App\Modules\Quiz\Domain\Entities\QuizSessionEntity;
use App\Modules\Quiz\Domain\Repositories\QuizRepositoryInterface;
use App\Modules\Quiz\Domain\ValueObjects\QuestionType;
use App\Modules\Quiz\Domain\ValueObjects\QuizStatus;
use Exception;

class UpdateQuizSessionUseCase
{
    public function __construct(
        private QuizRepositoryInterface $repository
    ) {}

    public function execute(UpdateQuizSessionDTO $dto): QuizSessionEntity
    {
        $sessionEntity = $this->repository->findSessionById($dto->getSessionId());

        if (!$sessionEntity) {
            throw new Exception("Quiz session not found.");
        }

        if ($sessionEntity->getStatus()->getValue() !== 'PENDING') {
            throw new Exception("error de modification.");
        }

        // Map questions
        $questions = [];
        foreach ($dto->getQuestions() as $qData) {
            $contextData = $qData['context_data'] ?? null;
            if (is_string($contextData)) {
                $contextData = json_decode($contextData, true);
            }

            $questions[] = new QuestionEntity(
                null, 
                $dto->getSessionId(),
                new QuestionType($qData['type']),
                $qData['content'],
                $qData['correct_answer'] ?? null,
                $contextData
            );
        }

        // Create updated entity
        $updatedSession = new QuizSessionEntity(
            $dto->getSessionId(),
            $sessionEntity->getFormateurId(),
            $dto->getTitle(),
            $dto->getDescription(),
            $dto->getClassroomId(),
            $sessionEntity->getStatus(),
            $dto->getTimerMinutes(),
            $dto->getPassingScore(),
            $questions
        );

        // Delete existing questions first (to match original behavior)
        $this->repository->deleteQuestionsBySessionId($dto->getSessionId());

        return $this->repository->saveSession($updatedSession);
    }
}
