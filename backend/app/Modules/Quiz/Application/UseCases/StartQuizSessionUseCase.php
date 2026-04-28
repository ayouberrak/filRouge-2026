<?php

namespace App\Modules\Quiz\Application\UseCases;

use App\Modules\Quiz\Domain\Entities\QuizSessionEntity;
use App\Modules\Quiz\Domain\Repositories\QuizRepositoryInterface;
use App\Modules\Quiz\Domain\ValueObjects\QuizStatus;

class StartQuizSessionUseCase
{
    public function __construct(
        private QuizRepositoryInterface $repository
    ) {}

    public function execute(int $sessionId): QuizSessionEntity
    {
        $session = $this->repository->findSessionById($sessionId);
        
        if (!$session) {
            throw new \Exception("session invalid.");
        }

        if ($session->getStatus()->getValue() === 'ACTIVE') {
            return $session;
        }

        if ($session->getStatus()->getValue() !== 'PENDING') {
            throw new \Exception("error dans la start session");
        }

        $activeSession = new QuizSessionEntity(
            $session->getId(),
            $session->getFormateurId(),
            $session->getTitle(),
            $session->getDescription(),
            $session->getClassroomId(),
            new QuizStatus('ACTIVE'),
            $session->getTimerMinutes(),
            $session->getPassingScore(),
            $session->getQuestions()
        );

        return $this->repository->saveSession($activeSession);
    }
}
