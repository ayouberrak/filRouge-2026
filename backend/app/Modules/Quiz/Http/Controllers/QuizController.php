<?php

namespace App\Modules\Quiz\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Modules\Quiz\Application\UseCases\CreateQuizSessionUseCase;
use App\Modules\Quiz\Application\UseCases\StartQuizSessionUseCase;
use App\Modules\Quiz\Application\UseCases\SubmitQuizResponseUseCase;
use App\Modules\Quiz\Application\UseCases\ValidateBriefCompletionUseCase;
use App\Modules\Quiz\Application\UseCases\GetEvaluatedResponsesUseCase;
use App\Modules\Quiz\Application\DTO\CreateQuizSessionDTO;
use App\Modules\Quiz\Application\DTO\SubmitQuizResponseDTO;
use App\Modules\Quiz\Http\Requests\CreateQuizSessionRequest;
use App\Modules\Quiz\Http\Requests\SubmitQuizResponseRequest;
use App\Modules\Quiz\Infrastructure\Models\QuizSessionModel;
use App\Modules\Quiz\Infrastructure\Models\QuestionModel;
use Carbon\Carbon;
use Exception;

class QuizController
{
    public function __construct(
        private CreateQuizSessionUseCase $createQuizSessionUseCase,
        private StartQuizSessionUseCase $startQuizSessionUseCase,
        private SubmitQuizResponseUseCase $submitQuizResponseUseCase,
        private ValidateBriefCompletionUseCase $validateBriefCompletionUseCase,
        private GetEvaluatedResponsesUseCase $getEvaluatedResponsesUseCase
    ) {}

    /**
     * Create a new quiz session (Formateur)
     */
    public function createSession(CreateQuizSessionRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // ── Upsert: si une session existe déjà pour ce brief, la remplacer ──
            $existingSession = \App\Modules\Quiz\Infrastructure\Models\QuizSessionModel
                ::where('brief_id', $validated['brief_id'])
                ->latest()
                ->first();

            if ($existingSession) {
                $existingSession->update([
                    'timer_minutes' => $validated['timer_minutes'],
                    'passing_score' => $validated['passing_score'],
                    'start_at' => $validated['start_at'] ?? null,
                    'status' => 'PENDING',
                ]);

                // Récupérer les IDs des questions existantes
                $questionIds = \App\Modules\Quiz\Infrastructure\Models\QuestionModel
                    ::where('quiz_session_id', $existingSession->id)
                    ->pluck('id');

                // Supprimer les réponses d'étudiants liées
                \App\Modules\Quiz\Infrastructure\Models\StudentResponseModel
                    ::whereIn('question_id', $questionIds)->delete();

                // Supprimer les anciennes questions
                \App\Modules\Quiz\Infrastructure\Models\QuestionModel
                    ::whereIn('id', $questionIds)->delete();

                // Recréer les questions
                foreach ($validated['questions'] as $qData) {
                    $contextData = $qData['context_data'] ?? null;
                    if (is_string($contextData)) {
                        $contextData = json_decode($contextData, true);
                    }
                    \App\Modules\Quiz\Infrastructure\Models\QuestionModel::create([
                        'quiz_session_id' => $existingSession->id,
                        'type'            => $qData['type'],
                        'content'         => $qData['content'],
                        'correct_answer'  => $qData['correct_answer'] ?? null,
                        'context_data'    => $contextData,
                        'points'          => $qData['points'] ?? 10,
                    ]);
                }

                return response()->json([
                    'message' => 'Quiz session updated successfully',
                    'data'    => [
                        'id'       => $existingSession->id,
                        'brief_id' => $existingSession->brief_id,
                        'status'   => $existingSession->status,
                    ]
                ], 200);
            }

            $dto = new CreateQuizSessionDTO(
                $validated['brief_id'],
                Auth::id(),
                $validated['timer_minutes'],
                $validated['passing_score'],
                $validated['start_at'] ?? null,
                $validated['questions']
            );

            $session = $this->createQuizSessionUseCase->execute($dto);

            return response()->json([
                'message' => 'Quiz session created successfully',
                'data' => [
                    'id'       => $session->getId(),
                    'brief_id' => $session->getBriefId(),
                    'status'   => $session->getStatus()->getValue()
                ]
            ], 201);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Start a quiz session (Formateur or System)
     */
    public function startSession(Request $request, int $sessionId): JsonResponse
    {
        try {
            $session = $this->startQuizSessionUseCase->execute($sessionId);

            return response()->json([
                'message' => 'Quiz session started successfully',
                'data' => [
                    'id' => $session->getId(),
                    'status' => $session->getStatus()->getValue(),
                    'start_at' => $session->getStartedAt()?->format('Y-m-d H:i:s')
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Submit a student response to a quiz question
     */
    public function submitResponse(SubmitQuizResponseRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $question = QuestionModel::findOrFail($validated['question_id']);
            $session = QuizSessionModel::findOrFail($question->quiz_session_id);
            $session = $this->syncSessionStatus($session);

            if ($session->status === 'COMPLETED') {
                return response()->json(['error' => 'Le quiz est terminé. Vous ne pouvez plus soumettre de réponses.'], 403);
            }
            
            $dto = new SubmitQuizResponseDTO(
                $validated['question_id'],
                Auth::id(),
                $validated['response_text']
            );

            $response = $this->submitQuizResponseUseCase->execute($dto);

            return response()->json([
                'message' => 'Response submitted and evaluated successfully',
                'data' => [
                    'id' => $response->getId(),
                    'is_correct' => $response->isCorrect(),
                    'score' => $response->getScore(),
                    'feedback' => $response->getAiFeedback()
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Validate the completion of a brief (Livrable + Quiz)
     */
    public function validateBriefCompletion(Request $request, int $briefId): JsonResponse
    {
        try {
            $studentId = $request->input('student_id') ?? Auth::id();
            
            if (!$briefId || !$studentId) {
                return response()->json(['error' => 'Brief ID or Student ID is missing'], 400);
            }
            
            $status = $this->validateBriefCompletionUseCase->execute($briefId, (int)$studentId);

            return response()->json([
                'message' => 'Brief completion evaluation retrieved',
                'status' => $status
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get the active quiz session for a specific brief
     */
    public function getSessionByBrief(int $briefId): JsonResponse
    {
        try {
            $session = \App\Modules\Quiz\Infrastructure\Models\QuizSessionModel::where('brief_id', $briefId)
                ->orderByRaw("FIELD(status, 'ACTIVE') DESC")
                ->latest()
                ->first();

            if (!$session) {
                return response()->json(['error' => 'Aucune session de quiz active pour ce brief'], 404);
            }

            $session = $this->syncSessionStatus($session);

            return response()->json([
                'data' => [
                    'id' => $session->id,
                    'status' => $session->status,
                    'timer_minutes' => $session->timer_minutes,
                    'start_at' => optional($session->start_at)?->format('Y-m-d H:i:s'),
                    'is_accessible' => $session->status !== 'COMPLETED'
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get all questions for a specific quiz session
     */
    public function getQuestions(int $sessionId): JsonResponse
    {
        try {
            $session = QuizSessionModel::findOrFail($sessionId);
            $session = $this->syncSessionStatus($session);

            if ($session->status === 'COMPLETED') {
                return response()->json(['error' => 'Le quiz est terminé.'], 403);
            }

            $questions = \App\Modules\Quiz\Infrastructure\Models\QuestionModel::where('quiz_session_id', $sessionId)
                ->get()
                ->map(function($q) {
                    $contextData = is_string($q->context_data)
                        ? json_decode($q->context_data, true)
                        : ($q->context_data ?? []);

                    return [
                        'id'           => $q->id,
                        'content'      => $q->content,
                        'type'         => $q->type,
                        'points'       => $q->points,
                        'options'      => $contextData['options'] ?? [],
                        'context_data' => $contextData,
                    ];
                });

            return response()->json([
                'data' => $questions
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get evaluated responses for a student (Formateur only)
     */
    public function getStudentResponses(int $sessionId, int $studentId): JsonResponse
    {
        try {
            $responses = $this->getEvaluatedResponsesUseCase->execute($sessionId, $studentId);
            return response()->json(['data' => $responses]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }



    private function syncSessionStatus(QuizSessionModel $session): QuizSessionModel
    {
        $now = Carbon::now();
        $startAt = $session->start_at ? Carbon::parse($session->start_at) : null;

        if (!$startAt) {
            return $session;
        }

        $endAt = $startAt->copy()->addMinutes((int)$session->timer_minutes);

        if ($now->greaterThanOrEqualTo($endAt)) {
            if ($session->status !== 'COMPLETED') {
                $session->status = 'COMPLETED';
                $session->save();
            }
            return $session->fresh();
        }

        if ($now->greaterThanOrEqualTo($startAt) && $session->status === 'PENDING') {
            $session->status = 'ACTIVE';
            $session->save();
            return $session->fresh();
        }

        return $session;
    }
}
