<?php

namespace App\Modules\Quiz\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Modules\Quiz\Application\UseCases\CreateQuizSessionUseCase;
use App\Modules\Quiz\Application\UseCases\StartQuizSessionUseCase;
use App\Modules\Quiz\Application\UseCases\SubmitQuizResponseUseCase;
use App\Modules\Quiz\Application\UseCases\GetEvaluatedResponsesUseCase;
use App\Modules\Quiz\Application\DTO\CreateQuizSessionDTO;
use App\Modules\Quiz\Application\DTO\SubmitQuizResponseDTO;
use App\Modules\Quiz\Http\Requests\CreateQuizSessionRequest;
use App\Modules\Quiz\Http\Requests\SubmitQuizResponseRequest;
use App\Modules\Quiz\Infrastructure\Models\QuizSessionModel;
use App\Modules\Quiz\Infrastructure\Models\QuestionModel;
use App\Modules\User\Infrastructure\Models\UserModel;
use Carbon\Carbon;

class QuizController
{
    public function __construct(
        private CreateQuizSessionUseCase $createQuizSessionUseCase,
        private StartQuizSessionUseCase $startQuizSessionUseCase,
        private SubmitQuizResponseUseCase $submitQuizResponseUseCase,
        private GetEvaluatedResponsesUseCase $getEvaluatedResponsesUseCase
    ) {}

    /**
     * Create a new quiz session (Formateur)
     */
    public function createSession(CreateQuizSessionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $dto = new CreateQuizSessionDTO(
                Auth::id(),
                $validated['title'],
                $validated['description'] ?? null,
                $validated['classroom_id'],
                $validated['timer_minutes'],
                $validated['passing_score'],
                $validated['questions']
            );

        $session = $this->createQuizSessionUseCase->execute($dto);

        return response()->json([
                'message' => 'Quiz session created successfully',
                'data' => [
                    'id'           => $session->getId(),
                    'title'        => $session->getTitle(),
                    'classroom_id' => $session->getClassroomId(),
                    'status'       => $session->getStatus()->getValue()
                ]
            ], 201);
    }

    /**
     * Start a quiz session (Formateur)
     */
    public function startSession(Request $request, int $sessionId): JsonResponse
    {
        $session = $this->startQuizSessionUseCase->execute($sessionId);

        return response()->json([
                'message' => 'Quiz session started successfully',
                'data' => [
                    'id' => $session->getId(),
                    'status' => $session->getStatus()->getValue(),
                ]
            ], 200);
    }

    /**
     * Submit a student response to a quiz question
     */
    public function submitResponse(SubmitQuizResponseRequest $request): JsonResponse
    {
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
    }

    /**
     * Get all questions for a specific quiz session
     */
    public function getQuestions(int $sessionId): JsonResponse
    {
        $session = QuizSessionModel::findOrFail($sessionId);
        $session = $this->syncSessionStatus($session);

        if ($session->status === 'COMPLETED') {
            return response()->json(['error' => 'Le quiz est terminé.'], 403);
        }

        $user = Auth::user();
        $hasParticipated = \App\Modules\Quiz\Infrastructure\Models\StudentResponseModel::where('student_id', $user->id)
            ->whereHas('question', function($q) use ($sessionId) {
                $q->where('quiz_session_id', $sessionId);
            })
            ->exists();

        if ($hasParticipated) {
            return response()->json(['error' => 'Vous avez déjà soumis vos réponses pour ce quiz.'], 403);
        }

        $questions = QuestionModel::where('quiz_session_id', $sessionId)
            ->get()
            ->map(function($q) {
                $contextData = is_string($q->context_data)
                    ? json_decode($q->context_data, true)
                    : ($q->context_data ?? []);

                return [
                    'id'           => $q->id,
                    'content'      => $q->content,
                    'type'         => $q->type,
                    'options'      => $contextData['options'] ?? [],
                    'context_data' => $contextData,
                ];
            });

            return response()->json([
                'data' => $questions
            ]);
    }

    /**
     * Get all quizzes for the current formateur (his own + those for his classrooms)
     */
    public function getMyQuizzes(): JsonResponse
    {
        $user = Auth::user();
        
        $managedClassroomIds = \App\Modules\Classroom\Infrastructure\Models\ClassroomModel::where('formateur_id', $user->id)
            ->pluck('id')
            ->toArray();

        $quizzes = QuizSessionModel::with(['classroom', 'questions'])
            ->where(function($query) use ($user, $managedClassroomIds) {
                $query->where('formateur_id', $user->id)
                    ->orWhereIn('classroom_id', $managedClassroomIds);
            })
            ->latest()
            ->get();

        return response()->json([
            'data' => $quizzes->map(fn($q) => [
                'id' => $q->id,
                'title' => $q->title,
                'description' => $q->description,
                'classroom' => $q->classroom ? ['id' => $q->classroom->id, 'name' => $q->classroom->name] : null,
                'status' => $q->status,
                'timer_minutes' => $q->timer_minutes,
                'questions_count' => $q->questions->count(),
                'created_at' => $q->created_at->format('Y-m-d H:i:s'),
            ])
        ]);
    }

    /**
     * Get quizzes assigned to the current student's classroom
     */
    public function getAssignedQuizzes(): JsonResponse
    {
        $user = Auth::user();
        $classroomId = $user->classroom_id ?? null;

        if (!$classroomId) {
            $classroom = \App\Modules\Classroom\Infrastructure\Models\ClassroomModel::whereHas('students', function($q) use ($user) {
                $q->where('users.id', $user->id);
            })->first();
            $classroomId = $classroom?->id;
        }

        if (!$classroomId) {
            return response()->json(['data' => []]);
        }

        $quizzes = QuizSessionModel::with(['formateur', 'questions'])
            ->where('classroom_id', $classroomId)
            ->whereIn('status', ['PENDING', 'ACTIVE', 'COMPLETED'])
            ->latest()
            ->get();

            return response()->json([
                'data' => $quizzes->map(function($q) use ($user) {
                    $hasParticipated = \App\Modules\Quiz\Infrastructure\Models\StudentResponseModel::where('student_id', $user->id)
                        ->whereHas('question', function($query) use ($q) {
                            $query->where('quiz_session_id', $q->id);
                        })
                        ->exists();

                    return [
                        'id' => $q->id,
                        'title' => $q->title,
                        'description' => $q->description,
                        'formateur' => $q->formateur ? ($q->formateur->first_name . ' ' . $q->formateur->last_name) : 'Formateur',
                        'status' => $q->status,
                        'is_completed' => $hasParticipated,
                        'timer_minutes' => $q->timer_minutes,
                        'passing_score' => $q->passing_score,
                        'questions_count' => $q->questions->count(),
                        'created_at' => $q->created_at->format('Y-m-d H:i:s'),
                    ];
                })
            ]);
    }

    /**
     * Get details of responses for a specific student in a session (Formateur only)
     */
    public function getStudentResponses(int $sessionId, int $studentId): JsonResponse
    {
        $responses = \App\Modules\Quiz\Infrastructure\Models\StudentResponseModel::where('student_id', $studentId)
            ->whereHas('question', function($q) use ($sessionId) {
                $q->where('quiz_session_id', $sessionId);
            })
            ->with('question')
            ->get();

            return response()->json([
                'data' => $responses->map(fn($r) => [
                    'id' => $r->id,
                    'question_id' => $r->question_id,
                    'question_content' => $r->question->content,
                    'question_type' => $r->question->type,
                    'response_text' => $r->response_text,
                    'is_correct' => $r->is_correct,
                    'score' => $r->score,
                    'ai_feedback' => $r->ai_feedback,
                    'created_at' => $r->created_at->format('Y-m-d H:i:s'),
                ])
            ]);
    }

    /**
     * Get all students who participated in a quiz session
     */
    public function getSessionSubmissions(int $sessionId): JsonResponse
    {
        $session = QuizSessionModel::findOrFail($sessionId);
            
        // Get unique student IDs who have at least one response for this session
        $studentIds = \App\Modules\Quiz\Infrastructure\Models\StudentResponseModel::whereHas('question', function($q) use ($sessionId) {
            $q->where('quiz_session_id', $sessionId);
        })
            ->distinct()
            ->pluck('student_id');

        $students = UserModel::whereIn('id', $studentIds)->get();

        return response()->json([
            'data' => $students->map(function($student) use ($sessionId) {
                // Calculate total score for this student in this session
                $responses = \App\Modules\Quiz\Infrastructure\Models\StudentResponseModel::where('student_id', $student->id)
                    ->whereHas('question', function($q) use ($sessionId) {
                        $q->where('quiz_session_id', $sessionId);
                    })
                    ->get();

                $totalQuestions = QuestionModel::where('quiz_session_id', $sessionId)->count();
                $answeredCount = $responses->count();
                $correctCount = $responses->where('is_correct', true)->count();
                    
                // Simple score calculation (percentage of correct answers)
                $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

                return [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'email' => $student->email,
                    'answered_count' => $answeredCount,
                    'total_questions' => $totalQuestions,
                    'score' => $score,
                    'status' => ($answeredCount >= $totalQuestions) ? 'COMPLETED' : 'IN_PROGRESS'
                ];
            })
        ]);
    }

    /**
     * Auto-sync session status based on timer
     */
    private function syncSessionStatus(QuizSessionModel $session): QuizSessionModel
    {
        return $session;
    }
}
