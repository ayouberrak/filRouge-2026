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
        $sessionId = $this->findSessionIdByQuestionId($dto->getQuestionId());
        $session = $this->quizRepository->findSessionById($sessionId);

        if (!$session || $session->getStatus()->getValue() !== 'ACTIVE') {
            throw new \Exception("error dans la submission");
        }

        $question = null;
        foreach ($session->getQuestions() as $q) {
            if ($q->getId() === $dto->getQuestionId()) {
                $question = $q;
                break;
            }
        }

        if (!$question) {
            throw new \Exception("error dans la question ");
        }

        $score = 0;
        $isCorrect = false;
        $feedback = "";

        if ($question->getType()->getValue() === 'multiple_choice') {
            $studentAnswer = trim(strtolower($dto->getResponseText()));
            $targetAnswer = trim(strtolower((string)$question->getCorrectAnswer()));
            
            $isCorrect = ($studentAnswer === $targetAnswer);

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
        else {
                $scenario = $question->getContextData()['scenario'] ?? $question->getContent();
                $aiResult = $this->aiClient->evaluateCode($scenario, $dto->getResponseText());
                $isCorrect = ($aiResult['score'] ?? 0) >= 70;
                $score = $aiResult['score'] ?? 0;
                $feedback = $aiResult['feedback'] ?? "Évaluation IA en cours.";

        }

        // 4. Enregistrer la réponse
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

    private function findSessionIdByQuestionId(int $questionId): int
    {
        $question = \App\Modules\Quiz\Infrastructure\Models\QuestionModel::findOrFail($questionId);
        return $question->quiz_session_id;
    }
}
