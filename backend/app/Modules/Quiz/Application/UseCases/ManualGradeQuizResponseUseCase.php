<?php

namespace App\Modules\Quiz\Application\UseCases;

use App\Modules\Quiz\Infrastructure\Models\StudentResponseModel;
use Illuminate\Support\Facades\Log;

class ManualGradeQuizResponseUseCase
{
    public function execute(int $responseId, int $score, string $feedback): StudentResponseModel
    {
        $response = StudentResponseModel::findOrFail($responseId);

        // Mettre à jour la réponse
        // On considère que si le score est >= 70, la réponse est correcte
        $response->update([
            'score' => $score,
            'is_correct' => $score >= 70,
            'ai_feedback' => "[Validation Manuelle] " . $feedback
        ]);

        Log::info("Manual grading for response {$responseId} : Score {$score}");

        return $response;
    }
}
