<?php

namespace App\Modules\Quiz\Application\UseCases;

class StartQuizSessionUseCase
{
    public function __construct(
        private \App\Modules\Quiz\Domain\Repositories\QuizRepositoryInterface $repository
    ) {}

    public function execute(int $sessionId): \App\Modules\Quiz\Domain\Entities\QuizSessionEntity
    {
        $session = $this->repository->findSessionById($sessionId);
        
        if (!$session) {
            throw new \Exception("La session de quiz avec l'ID $sessionId n'a pas été trouvée.");
        }

        if ($session->getStatus()->getValue() !== 'PENDING') {
            throw new \Exception("On ne peut démarrer qu'une session à l'état PENDING. Statut actuel: " . $session->getStatus()->getValue());
        }

        // Création d'une nouvelle Entité avec le statut ACTIVE et la date de démarrage
        $activeSession = new \App\Modules\Quiz\Domain\Entities\QuizSessionEntity(
            $session->getId(),
            $session->getBriefId(),
            $session->getFormateurId(),
            new \App\Modules\Quiz\Domain\ValueObjects\QuizStatus('ACTIVE'),
            $session->getTimerMinutes(),
            $session->getPassingScore(),
            new \DateTime(), // start_at défini à maintenant
            null,
            $session->getQuestions()
        );

        return $this->repository->saveSession($activeSession);
    }
}
