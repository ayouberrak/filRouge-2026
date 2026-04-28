<?php

namespace App\Modules\Quiz\Application\UseCases;

use App\Modules\Quiz\Application\DTO\CreateQuizSessionDTO;
use App\Modules\Quiz\Domain\Entities\QuestionEntity;
use App\Modules\Quiz\Domain\Entities\QuizSessionEntity;
use App\Modules\Quiz\Domain\Repositories\QuizRepositoryInterface;
use App\Modules\Quiz\Domain\ValueObjects\QuestionType;
use App\Modules\Quiz\Domain\ValueObjects\QuizStatus;

class CreateQuizSessionUseCase
{
    public function __construct(
        private QuizRepositoryInterface $repository
    ) {}

    public function execute(CreateQuizSessionDTO $dto): QuizSessionEntity
    {
        $questions = [];
        foreach ($dto->getQuestions() as $qData) {
            $contextData = $qData['context_data'] ?? null;
            if (is_string($contextData)) {
                $contextData = json_decode($contextData, true);
            }

            $questions[] = new QuestionEntity(
                null,
                null,
                new QuestionType($qData['type']),
                $qData['content'],
                $qData['correct_answer'] ?? null,
                $contextData
            );
        }

        $session = new QuizSessionEntity(
            null,
            $dto->getFormateurId(),
            $dto->getTitle(),
            $dto->getDescription(),
            $dto->getClassroomId(),
            new QuizStatus('PENDING'),
            $dto->getTimerMinutes(),
            $dto->getPassingScore(),
            $questions
        );

        return $this->repository->saveSession($session);
    }
}
