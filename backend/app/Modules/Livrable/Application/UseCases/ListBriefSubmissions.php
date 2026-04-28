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

    public function execute(int $briefId): array
    {
        $brief = BriefModel::with([
            'classrooms.students.squad', 
            'squads.members.squad'
        ])->find($briefId);
        
        if (!$brief) {
            return [];
        }

        $classroomStudents = $brief->classrooms->flatMap(function($classroom) {
            return $classroom->students;
        });

        $squadStudents = $brief->squads->flatMap(function($squad) {
            return $squad->members;
        });
        $students = $classroomStudents->merge($squadStudents)->unique('id');
        
        $livrables = $this->livrableRepository->findByBriefId($briefId);
        $livrablesMap = [];
        $squadLivrablesMap = [];
        
        foreach ($livrables as $l) {
            if ($l->getStudentId()) {
                $livrablesMap[$l->getStudentId()] = $l;
            }
            if ($l->getSquadId()) {
                $squadLivrablesMap[$l->getSquadId()] = $l;
            }
        }

        return $students->map(function(UserModel $student) use ($brief, $livrablesMap, $squadLivrablesMap) {
            $studentId = (int)$student->id;
            
            $submission = $livrablesMap[$studentId] ?? null;
            if (!$submission && $brief->modality === 'GROUP' && $student->squad_id) {
                $submission = $squadLivrablesMap[(int)$student->squad_id] ?? null;
            }
            
            return [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'avatar' => $student->avatar ?? 'https://avatar.cc/100?u=' . $student->id,
                'squad_name' => $student->squad?->name,
                'submission' => $submission ? [
                    'id' => $submission->getId(),
                    'date' => $submission->getUpdatedAt() ? $submission->getUpdatedAt()->diffForHumans() : 'Date inconnue',
                    'url' => $submission->getLink(),
                    'message' => $submission->getMessage(),
                    'status' => $submission->getStatus()->getValue(),
                    'formateur_id' => $submission->getFormateurId(),
                    'formateur_message' => $submission->getFormateurMessage()
                ] : null
            ];
        })->toArray();
    }
}
