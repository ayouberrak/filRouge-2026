<?php

namespace App\Modules\Quiz\Application\UseCases;

class CreateQuizSessionUseCase
{
    public function __construct(
        private \App\Modules\Quiz\Domain\Repositories\QuizRepositoryInterface $repository
    ) {}

    public function execute(\App\Modules\Quiz\Application\DTO\CreateQuizSessionDTO $dto): \App\Modules\Quiz\Domain\Entities\QuizSessionEntity
    {
        $questions = [];
        foreach ($dto->getQuestions() as $qData) {
            $questions[] = new \App\Modules\Quiz\Domain\Entities\QuestionEntity(
                null,
                null, // L'ID de session sera défini lors du save()
                new \App\Modules\Quiz\Domain\ValueObjects\QuestionType($qData['type']),
                $qData['content'],
                $qData['correct_answer'] ?? null,
                $qData['context_data'] ?? null,
                $qData['points'] ?? 10
            );
        }

        $session = new \App\Modules\Quiz\Domain\Entities\QuizSessionEntity(
            null,
            $dto->getBriefId(),
            $dto->getFormateurId(),
            new \App\Modules\Quiz\Domain\ValueObjects\QuizStatus('PENDING'),
            $dto->getTimerMinutes(),
            $dto->getPassingScore(),
            null,
            null,
            $questions
        );

        return $this->repository->saveSession($session);
    }
}
