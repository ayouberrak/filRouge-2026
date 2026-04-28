<?php

namespace App\Modules\Quiz\Application\UseCases;

use App\Modules\Quiz\Infrastructure\Models\StudentResponseModel;
use App\Modules\Quiz\Infrastructure\Models\QuestionModel;
use App\Modules\Quiz\Infrastructure\AI\MCPClient;
use Illuminate\Support\Collection;

class GetEvaluatedResponsesUseCase
{
    public function __construct(
        private MCPClient $aiClient
    ) {}

    public function execute(int $sessionId, int $studentId): Collection
    {
        $responses = StudentResponseModel::whereRelation('question','quiz_session_id', $sessionId)//charge la relation aussi
        ->where('student_id', $studentId)->get();

        foreach ($responses as $response) {
            $needsAI = in_array($response->question->type, ['code_simulation', 'open_ended'])&& ($response->ai_feedback === null);

            if ($needsAI) {
                    $contextData = json_decode($response->question->context_data, true) ?? [];
                    $scenario = $contextData['scenario'] ?? $response->question->content;

                    $aiResult = $this->aiClient->evaluateCode(
                        $scenario,
                        $response->response_text
                    );

                    $response->update([
                        'score' => $aiResult['score'],
                        'is_correct' => $aiResult['is_correct'],
                        'ai_feedback' => $aiResult['feedback']
                    ]);
            }
        }

        return $responses->map(function($response) {
            return [
                'id' => $response->id,
                'question_content' => $response->question->content ?? '',
                'question_type' => $response->question->type ?? 'multiple_choice',
                'response_text' => $response->response_text,
                'score' => $response->score,
                'is_correct' => (bool) $response->is_correct,
                'ai_feedback' => $response->ai_feedback,
                'max_points' => $response->question->points ?? 10,
            ];
        });
    }
}
