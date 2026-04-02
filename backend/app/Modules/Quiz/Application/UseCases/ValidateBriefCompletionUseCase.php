<?php

namespace App\Modules\Quiz\Application\UseCases;

use Illuminate\Support\Facades\DB;

class ValidateBriefCompletionUseCase
{
    public function __construct(
        private \App\Modules\Quiz\Domain\Repositories\QuizRepositoryInterface $quizRepository
    ) {}

    public function execute(int $briefId, int $studentId): string
    {
        // ----------------------------------------------------
        // LOGIQUE MATRICE DE VALIDATION DDD
        // ----------------------------------------------------
        
        // 1. Vérification du Livrable (Pratique)
        $livrable = DB::table('livrables')
            ->where('brief_id', $briefId)
            ->where('student_id', $studentId)
            ->first();

        // 2. Vérification de la Session de Quiz (Théorie Globale)
        // On récupère la dernière session complétée ou active pour ce brief (tri descendant)
        $sessionModel = \App\Modules\Quiz\Infrastructure\Models\QuizSessionModel::with('questions')
            ->where('brief_id', $briefId)
            ->orderBy('id', 'desc')
            ->first();

        // 3. Analyse des conditions d'échec immédiat
        if (!$livrable || $livrable->status === 'SUBMITTED') {
            return "PENDING"; // En attente de correction du formateur sur le livrable
        }

        if (!$sessionModel) {
            return "PENDING_QUIZ"; // Pas encore de passage de quiz
        }

        // Calcul exact du score de l'étudiant
        $totalQuestions = $sessionModel->questions->count();
        if ($totalQuestions === 0) return "VALIDATED"; // Pas de questions = succès d'office

        // On récupère toutes les réponses apportées dans le cadre de cette session
        $responses = $this->quizRepository->findResponsesBySessionId($sessionModel->id);
        
        // Trouver la réponse de CET étudiant sur chaque question
        $totalAchievedScore = 0;
        foreach ($sessionModel->questions as $question) {
            $matchingResponse = array_filter($responses, fn($r) => 
                $r->getQuestionId() === $question->id && $r->getStudentId() === $studentId
            );
            $response = reset($matchingResponse); // Première correspondance s'il y en a une

            if ($response) {
                // Modération du score en fonction des points alloués à la question (si c'était sur 100 on normalise)
                $normalizedScore = ($response->getScore() / 100) * $question->points;
                $totalAchievedScore += $normalizedScore;
            }
        }

        // Quel était le max de points atteignables (pour faire le ratio %)
        $maxPossiblePoints = $sessionModel->questions->sum('points');
        $finalPercentage = $maxPossiblePoints > 0 ? ($totalAchievedScore / $maxPossiblePoints) * 100 : 0;

        // 4. Test Final Conditionnel
        $livrableIsValidated = ($livrable->status === 'VALIDATED');
        $quizPassed = ($finalPercentage >= $sessionModel->passing_score);

        // TABLE DE VÉRITÉ DDD
        if ($livrableIsValidated && $quizPassed) {
            // Optionnel : Clôturer la session si c'est bon
            if ($sessionModel->status !== 'completed') {
                $sessionModel->update(['status' => 'completed', 'end_at' => now()]);
            }
            return "VALIDATED"; 
        }

        if (!$livrableIsValidated && $quizPassed) {
            return "REJECTED_LIVRABLE"; 
        }

        if ($livrableIsValidated && !$quizPassed) {
            // Quiz raté mais code projet correct.
            // On le force à se clore pour bloquer d'autres try.
            if ($sessionModel->status !== 'completed') {
                $sessionModel->update(['status' => 'completed', 'end_at' => now()]);
            }
            return "REJECTED_QUIZ"; 
        }

        return "REJECTED"; // Les deux ont échoué.
    }
}
