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
use App\Modules\Quiz\Application\DTO\CreateQuizSessionDTO;
use App\Modules\Quiz\Application\DTO\SubmitQuizResponseDTO;
use App\Modules\Quiz\Http\Requests\CreateQuizSessionRequest;
use App\Modules\Quiz\Http\Requests\SubmitQuizResponseRequest;
use Exception;

class QuizController
{
    public function __construct(
        private CreateQuizSessionUseCase $createQuizSessionUseCase,
        private StartQuizSessionUseCase $startQuizSessionUseCase,
        private SubmitQuizResponseUseCase $submitQuizResponseUseCase,
        private ValidateBriefCompletionUseCase $validateBriefCompletionUseCase
    ) {}

    /**
     * Create a new quiz session (Formateur)
     */
    public function createSession(CreateQuizSessionRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $dto = new CreateQuizSessionDTO(
                $validated['brief_id'],
                Auth::id(),
                $validated['timer_minutes'],
                $validated['passing_score'],
                $validated['questions']
            );

            $session = $this->createQuizSessionUseCase->execute($dto);

            return response()->json([
                'message' => 'Quiz session created successfully',
                'data' => [
                    'id' => $session->getId(),
                    'brief_id' => $session->getBriefId(),
                    'status' => $session->getStatus()->getValue()
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Start a quiz session (Formateur or System)
     */
    public function startSession(Request $request, int $id): JsonResponse
    {
        try {
            $session = $this->startQuizSessionUseCase->execute($id);

            return response()->json([
                'message' => 'Quiz session started successfully',
                'data' => [
                    'id' => $session->getId(),
                    'status' => $session->getStatus()->getValue(),
                    'start_at' => $session->getStartedAt()?->format('Y-m-d H:i:s')
                ]
            ], 200);
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Validate the completion of a brief (Livrable + Quiz)
     */
    public function validateBriefCompletion(Request $request, int $briefId): JsonResponse
    {
        try {
            // S'il s'agit d'une vérification formateur sur un étudiant précis ou par défaut l'étudiant lui-même
            $studentId = $request->input('student_id', Auth::id());
            
            $status = $this->validateBriefCompletionUseCase->execute($briefId, $studentId);

            return response()->json([
                'message' => 'Brief completion evaluation retrieved',
                'status' => $status
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
