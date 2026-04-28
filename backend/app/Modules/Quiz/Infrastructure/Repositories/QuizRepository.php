<?php

namespace App\Modules\Quiz\Infrastructure\Repositories;

use App\Modules\Quiz\Domain\Repositories\QuizRepositoryInterface;
use App\Modules\Quiz\Domain\Entities\QuizSessionEntity;
use App\Modules\Quiz\Domain\Entities\QuestionEntity;
use App\Modules\Quiz\Domain\Entities\ResponseEntity;
use App\Modules\Quiz\Domain\ValueObjects\QuizStatus;
use App\Modules\Quiz\Domain\ValueObjects\QuestionType;
use App\Modules\Quiz\Infrastructure\Models\QuizSessionModel;
use App\Modules\Quiz\Infrastructure\Models\QuestionModel;
use App\Modules\Quiz\Infrastructure\Models\StudentResponseModel;

class QuizRepository implements QuizRepositoryInterface
{
    public function saveSession(QuizSessionEntity $session): QuizSessionEntity
    {
        $data = [
            'formateur_id' => $session->getFormateurId(),
            'title' => $session->getTitle(),
            'description' => $session->getDescription(),
            'classroom_id' => $session->getClassroomId(),
            'status' => $session->getStatus()->getValue(),
            'timer_minutes' => $session->getTimerMinutes(),
            'passing_score' => $session->getPassingScore(),
        ];

        if ($session->getId()) {
            $model = QuizSessionModel::findOrFail($session->getId());
            $model->update($data);
        } else {
            $model = QuizSessionModel::create($data);
        }

        // Save Questions
        foreach ($session->getQuestions() as $question) {
            $this->saveQuestion($model->id, $question);
        }

        return $this->toSessionEntity($model->fresh(['questions']));
        //fresh recharge le model de base de donnes 
    }

    private function saveQuestion(int $sessionId, QuestionEntity $question): void
    {
        $data = [
            'quiz_session_id' => $sessionId,
            'type' => $question->getType()->getValue(),
            'content' => $question->getContent(),
            'correct_answer' => $question->getCorrectAnswer(),
            'context_data' => $question->getContextData(),
        ];

        if ($question->getId() && QuestionModel::find($question->getId())) {
            QuestionModel::where('id', $question->getId())->update($data);
        } else {
            QuestionModel::create($data);
        }
    }

    public function findSessionById(int $id): ?QuizSessionEntity
    {
        $model = QuizSessionModel::with('questions')->find($id);
        return $model ? $this->toSessionEntity($model) : null;
    }

    public function saveResponse(ResponseEntity $response): ResponseEntity
    {
        $data = [
            'question_id' => $response->getQuestionId(),
            'student_id' => $response->getStudentId(),
            'response_text' => $response->getResponseText(),
            'score' => $response->getScore(),
            'is_correct' => $response->isCorrect(),
            'ai_feedback' => $response->getAiFeedback(),
        ];

        if ($response->getId()) {
            $model = StudentResponseModel::findOrFail($response->getId());
            $model->update($data);
        } else {
            $model = StudentResponseModel::create($data);
        }

        return $this->toResponseEntity($model);
    }

    public function findResponsesBySessionId(int $sessionId): array
    {
        $models = StudentResponseModel::whereHas('question', function($query) use ($sessionId) {
            $query->where('quiz_session_id', $sessionId);
        })->get();

        return $models->map(fn(StudentResponseModel $m) => $this->toResponseEntity($m))->toArray();
    }

    public function findResponseByQuestionAndStudent(int $questionId, int $studentId): ?ResponseEntity
    {
        $model = StudentResponseModel::where('question_id', $questionId)
            ->where('student_id', $studentId)
            ->first();
        
        return $model ? $this->toResponseEntity($model) : null;
    }

    private function toSessionEntity(QuizSessionModel $model): QuizSessionEntity
    {
        $questions = $model->questions->map(fn($q) => new QuestionEntity(
            $q->id,
            $q->quiz_session_id,
            new QuestionType($q->type),
            $q->content,
            $q->correct_answer,
            $q->context_data
        ))->toArray();

        return new QuizSessionEntity(
            $model->id,
            $model->formateur_id,
            $model->title,
            $model->description,
            $model->classroom_id,
            new QuizStatus($model->status),
            $model->timer_minutes,
            $model->passing_score,
            $questions
        );
    }

    private function toResponseEntity(StudentResponseModel $model): ResponseEntity
    {
        return new ResponseEntity(
            $model->id,
            $model->question_id,
            $model->student_id,
            $model->response_text,
            $model->score,
            $model->is_correct,
            $model->ai_feedback
        );
    }
}
