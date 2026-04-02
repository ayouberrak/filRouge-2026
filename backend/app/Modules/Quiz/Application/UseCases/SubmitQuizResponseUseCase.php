<?php

namespace App\Modules\Quiz\Application\UseCases;

use App\Modules\Quiz\Domain\Repositories\QuizRepositoryInterface;
use App\Modules\Quiz\Infrastructure\AI\MCPClient;
use App\Modules\Quiz\Application\DTO\SubmitQuizResponseDTO;
use App\Modules\Quiz\Domain\Entities\ResponseEntity;

class SubmitQuizResponseUseCase
{
    public function __construct(
        private QuizRepositoryInterface $quizRepository,
        private MCPClient $aiClient
    ) {}

    public function execute(SubmitQuizResponseDTO $dto): ResponseEntity
    {
        // 1. Trouver le brief concerné et vérifier l'existence d'une session active
        $briefId = $this->findBriefIdByQuestionId($dto->getQuestionId());
        $session = $this->quizRepository->findActiveSessionByBriefId($briefId);

        if (!$session) {
            throw new \Exception("Aucune session active ou en cours de validation n'a été trouvée pour répondre à cette question.");
        }

        // 2. Extraire la question spécifique de la session
        $question = null;
        foreach ($session->getQuestions() as $q) {
            if ($q->getId() === $dto->getQuestionId()) {
                $question = $q;
                break;
            }
        }

        if (!$question) {
            throw new \Exception("Question introuvable dans cette session active.");
        }

        // 3. Procédure d'évaluation
        $score = 0;
        $isCorrect = false;
        $feedback = "";

        // Cas A : QCM simple (Validation stricte)
        if ($question->getType()->getValue() === 'multiple_choice') {
            $isCorrect = (trim(strtolower($dto->getResponseText())) === trim(strtolower((string)$question->getCorrectAnswer())));
            $score = $isCorrect ? 100 : 0;
            $feedback = $isCorrect ? "Bonne réponse !" : "Mauvaise réponse. La réponse attendue était : " . $question->getCorrectAnswer();
        } 
        // Cas B : Simulation de Code (Évaluation IA Gemini)
        else {
            $aiResult = $this->aiClient->evaluateCode($question->getContent(), $dto->getResponseText());
            $score = $aiResult['score'] ?? 0;
            $isCorrect = $aiResult['is_correct'] ?? false;
            $feedback = $aiResult['feedback'] ?? "Aucun retour généré par l'IA.";
        }

        // 4. Créer et enregistrer la réponse évaluée
        $response = new ResponseEntity(
            null,
            $dto->getQuestionId(),
            $dto->getStudentId(),
            $dto->getResponseText(),
            $score,
            $isCorrect,
            $feedback
        );

        return $this->quizRepository->saveResponse($response);
    }

    private function findBriefIdByQuestionId(int $questionId): int
    {
        $question = \App\Modules\Quiz\Infrastructure\Models\QuestionModel::findOrFail($questionId);
        $session  = \App\Modules\Quiz\Infrastructure\Models\QuizSessionModel::findOrFail($question->quiz_session_id);
        return $session->brief_id;
    }
}
