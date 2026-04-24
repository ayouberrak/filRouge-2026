<?php

namespace App\Modules\Livrable\Application\UseCases;

use App\Modules\Brief\Infrastructure\Models\BriefModel;
use App\Modules\Livrable\Domain\Repositories\LivrableRepositoryInterface;
use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ListBriefSubmissions
{
    public function __construct(
        private LivrableRepositoryInterface $livrableRepository
    ) {}

    /**
     * Retourne la liste complète des étudiants et leurs rendus pour un brief donné
     */
    public function execute(int $briefId): array
    {
        Log::info("ListBriefSubmissions: Fetching for Brief ID " . $briefId);

        // 1. Récupérer le Brief avec ses classes et ses squads (en chargeant les relations nécessaires)
        $brief = BriefModel::with([
            'classrooms.students.squad', 
            'squads.members.squad'
        ])->find($briefId);
        
        if (!$brief) {
            Log::warning("ListBriefSubmissions: Brief not found for ID " . $briefId);
            return [];
        }

        // 2. Agréger tous les étudiants de toutes les classes rattachées au brief
        $classroomStudents = $brief->classrooms->flatMap(function($classroom) {
            return $classroom->students;
        });

        // 2bis. Agréger tous les étudiants de toutes les squads rattachées au brief
        $squadStudents = $brief->squads->flatMap(function($squad) {
            return $squad->members;
        });

        // Combiner et dédoublonner les étudiants
        $students = $classroomStudents->merge($squadStudents)->unique('id');

        $briefId = (int)$briefId;
        
        // 2. Récupérer la session de quiz pour ce brief
        $session = \DB::table('quiz_sessions')->where('brief_id', $briefId)
            ->orderByRaw("FIELD(status, 'ACTIVE') DESC")
            ->latest()
            ->first();
        
        $sessionId = $session ? $session->id : null;

        Log::info("ListBriefSubmissions: Found " . count($students) . " students.");

        // 3. Récupérer tous les livrables déjà soumis pour ce brief
        $livrables = $this->livrableRepository->findByBriefId($briefId);
        $livrablesMap = [];
        $squadLivrablesMap = [];
        
        foreach ($livrables as $l) {
            if ($l->getStudentId()) {
                $livrablesMap[(int)$l->getStudentId()] = $l;
            }
            if ($l->getSquadId()) {
                $squadLivrablesMap[(int)$l->getSquadId()] = $l;
            }
        }

        // 4. Mapper les étudiants avec leurs rendus
        return $students->map(function(UserModel $student) use ($brief, $livrablesMap, $squadLivrablesMap, $sessionId) {
            $studentId = (int)$student->id;
            $briefId = (int)$brief->id;
            
            // Chercher le rendu individuel ou le rendu de squad (si mode groupe)
            $submission = $livrablesMap[$studentId] ?? null;
            if (!$submission && $brief->modality === 'GROUP' && $student->squad_id) {
                $submission = $squadLivrablesMap[(int)$student->squad_id] ?? null;
            }
            
            if (!$submission) {
                Log::debug("ListBriefSubmissions: No submission found for Student ID {$student->id}.");
            }

            // Calcul du score de quiz réel via une jointure directe pour plus de fiabilité
            $quizScore = \DB::table('student_responses')
                ->join('questions', 'student_responses.question_id', '=', 'questions.id')
                ->join('quiz_sessions', 'questions.quiz_session_id', '=', 'quiz_sessions.id')
                ->where('student_responses.student_id', $student->id)
                ->where('quiz_sessions.brief_id', $briefId)
                ->sum('student_responses.score');

            return [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'avatar' => $student->avatar ?? 'https://avatar.cc/100?u=' . $student->id,
                'squad_name' => $student->squad?->name,
                'quiz_session_id' => $sessionId,
                'submission' => $submission ? [
                    'id' => $submission->getId(),
                    'date' => $submission->getUpdatedAt() ? $submission->getUpdatedAt()->diffForHumans() : 'Date inconnue',
                    'url' => $submission->getLink(),
                    'message' => $submission->getMessage(),
                    'status' => $submission->getStatus()->getValue(),
                    'formateur_id' => $submission->getFormateurId(),
                    'formateur_message' => $submission->getFormateurMessage(),
                    'quiz_score' => $quizScore
                ] : null
            ];
        })->toArray();
    }
}
