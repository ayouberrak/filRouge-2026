<?php

namespace App\Modules\Quiz\Application\UseCases;

use Illuminate\Support\Facades\DB;
use App\Modules\Quiz\Infrastructure\Models\QuizSessionModel;

class ValidateBriefCompletionUseCase
{
    public function __construct(
        private \App\Modules\Quiz\Domain\Repositories\QuizRepositoryInterface $quizRepository
    ) {}

    public function execute(int $briefId, int $studentId): array
    {
        $livrable = DB::table('livrables')
            ->where('brief_id', $briefId)
            ->where('student_id', $studentId)
            ->first();

        $sessionModel = QuizSessionModel::with('questions')
            ->where('brief_id', $briefId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$sessionModel) {
            return [
                'is_completed' => false, 
                'status' => 'PENDING_QUIZ',
                'quiz_score' => 0,
                'quiz_status' => 'AUCUN QUIZ CRÉÉ',
                'livrable_status' => $livrable ? ($livrable->status === 'VALIDATED' || $livrable->status === 'VALIDE' ? 'Validé' : 'À refaire') : 'En attente'
            ];
        }

        if (!$livrable) {
            return [
                'is_completed' => false, 
                'status' => 'PENDING_LIVRABLE',
                'quiz_score' => 0,
                'quiz_status' => 'LIVRABLE MANQUANT',
                'livrable_status' => 'Non soumis'
            ];
        }

        $totalQuestions = $sessionModel->questions->count();
        if ($totalQuestions === 0) {
            return [
                'is_completed' => true, 
                'status' => 'VALIDATED',
                'quiz_score' => 100,
                'quiz_status' => 'AUTO-VALIDÉ',
                'livrable_status' => $livrable->status === 'VALIDATED' || $livrable->status === 'VALIDE' ? 'Validé' : 'À refaire'
            ];
        }

        $responses = $this->quizRepository->findResponsesBySessionId($sessionModel->id);
        $studentResponses = array_filter($responses, fn($r) => $r->getStudentId() === $studentId);
        
        $totalAchievedScore = 0;
        $answeredQuestionsCount = 0;
        foreach ($sessionModel->questions as $question) {
            foreach ($studentResponses as $response) {
                if ($response->getQuestionId() === $question->id) {
                    $totalAchievedScore += $response->getScore(); // Assuming score is 0-100
                    $answeredQuestionsCount++;
                    break;
                }
            }
        }

        $finalPercentage = $totalQuestions > 0 ? ($totalAchievedScore / $totalQuestions) : 0;

        $livrableIsValidated = ($livrable->status === 'VALIDATED' || $livrable->status === 'VALIDE');
        
        // If student hasn't answered anything yet but the session is still active/available
        if (empty($studentResponses)) {
            return [
                'is_completed' => false,
                'status' => 'PENDING_QUIZ',
                'quiz_score' => 0,
                'quiz_status' => 'EN ATTENTE',
                'livrable_status' => $livrable->status === 'VALIDATED' || $livrable->status === 'VALIDE' ? 'Validé' : 'À refaire',
            ];
        }

        $quizPassed = ($finalPercentage >= $sessionModel->passing_score);
        $isCompleted = false;
        $label = "REJECTED";

        if ($livrableIsValidated && $quizPassed) {
            $isCompleted = true;
            $label = "VALIDATED"; 
        } elseif (!$livrableIsValidated && $quizPassed) {
            $label = "REJECTED_LIVRABLE"; 
        } elseif ($livrableIsValidated && !$quizPassed) {
            $label = "REJECTED_QUIZ"; 
        }

        return [
            'is_completed' => $isCompleted || $quizPassed,
            'status' => $label,
            'quiz_score' => round($finalPercentage),
            'quiz_status' => $quizPassed ? 'RÉUSSI' : 'ÉCHOUÉ',
            'livrable_status' => $livrable->status === 'VALIDATED' || $livrable->status === 'VALIDE' ? 'Validé' : 'À refaire',
        ];
    }
}
