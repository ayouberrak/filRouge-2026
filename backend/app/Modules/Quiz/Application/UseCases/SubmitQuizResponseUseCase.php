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
        \Illuminate\Support\Facades\Log::info("QuizSubmission: Processing student {$dto->getStudentId()} for brief {$briefId}");
        
        $session = $this->quizRepository->findActiveSessionByBriefId($briefId);

        if (!$session) {
            \Illuminate\Support\Facades\Log::warning("QuizSubmission: No active session found for brief {$briefId}");
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

        // Cas A : QCM simple (Validation stricte mais robuste)
        if ($question->getType()->getValue() === 'multiple_choice') {
            $studentAnswer = trim(strtolower($dto->getResponseText()));
            $targetAnswer = trim(strtolower((string)$question->getCorrectAnswer()));
            
            // 1. Direct match (Normalized)
            $isCorrect = ($studentAnswer === $targetAnswer);

            // 2. Fallback: If target is numeric (index), check against options
            if (!$isCorrect && is_numeric($targetAnswer)) {
                $options = $question->getContextData()['options'] ?? [];
                $index = (int)$targetAnswer;
                if (isset($options[$index])) {
                    $isCorrect = ($studentAnswer === trim(strtolower((string)$options[$index])));
                }
            }

            $score = $isCorrect ? 100 : 0;
            $feedback = $isCorrect ? "Bonne réponse !" : "Mauvaise réponse. La réponse attendue était : " . $question->getCorrectAnswer();
        } 
        // Cas B : Question ouverte / Mise en situation — Évaluation IA immédiate
        else {
            try {
                $scenario = $question->getContextData()['scenario'] ?? $question->getContent();
                $aiResult = $this->aiClient->evaluateCode($scenario, $dto->getResponseText());
                $isCorrect = ($aiResult['score'] ?? 0) >= 70;
                $score = $aiResult['score'] ?? 0;
                $feedback = $aiResult['feedback'] ?? "Évaluation IA en cours.";
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("AI evaluation failed for open_ended question: " . $e->getMessage());
                $score = 0;
                $isCorrect = false;
                $feedback = "⚠️ [Échec IA] L'évaluation automatique a rencontré un problème (Erreur API). Veuillez réessayer plus tard.";
            }
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

        $savedResponse = $this->quizRepository->saveResponse($response);



        return $savedResponse;
    }

    private function findBriefIdByQuestionId(int $questionId): int
    {
        $question = \App\Modules\Quiz\Infrastructure\Models\QuestionModel::findOrFail($questionId);
        $session  = \App\Modules\Quiz\Infrastructure\Models\QuizSessionModel::findOrFail($question->quiz_session_id);
        return $session->brief_id;
    }
}
