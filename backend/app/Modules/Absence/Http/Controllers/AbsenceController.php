<?php

namespace App\Modules\Absence\Http\Controllers;

use App\Modules\Absence\Application\UseCases\CreateAbsence;
use App\Modules\Absence\Application\UseCases\SubmitAbsenceJustification;
use App\Modules\Absence\Application\UseCases\ApproveAbsence;
use App\Modules\Absence\Application\UseCases\RejectAbsence;
use App\Modules\Absence\Application\UseCases\GetAllAbsences;
use App\Modules\Absence\Application\UseCases\GetAbsencesByStudent;
use App\Modules\Absence\Application\UseCases\GetAbsencesByClassroom;
use App\Modules\Absence\Application\UseCases\DeleteAbsence;
use App\Modules\Absence\Http\Requests\CreateAbsenceRequest;
use App\Modules\Absence\Http\Requests\JustifyAbsenceRequest;
use App\Modules\Absence\Http\Resources\AbsenceResource;
use Illuminate\Http\Request;

class AbsenceController
{
    public function __construct(
        private CreateAbsence $createAbsenceUseCase,
        private SubmitAbsenceJustification $submitAbsenceJustificationUseCase,
        private ApproveAbsence $approveAbsenceUseCase,
        private RejectAbsence $rejectAbsenceUseCase,
        private GetAllAbsences $getAllAbsencesUseCase,
        private GetAbsencesByStudent $getAbsencesByStudentUseCase,
        private GetAbsencesByClassroom $getAbsencesByClassroomUseCase,
        private DeleteAbsence $deleteAbsenceUseCase
    ) {}

    public function index()
    {
        $absences = $this->getAllAbsencesUseCase->execute();
        return response()->json([
            'absences' => AbsenceResource::collection($absences)
        ]);
    }

    public function getByStudent(int $studentId)
    {
        $absences = $this->getAbsencesByStudentUseCase->execute($studentId);
        return response()->json([
            'absences' => AbsenceResource::collection($absences)
        ]);
    }

    public function myAbsences(Request $request)
    {
        $absences = $this->getAbsencesByStudentUseCase->execute($request->user()->id);
        return response()->json([
            'absences' => AbsenceResource::collection($absences)
        ]);
    }

    public function getByClassroom(int $classroomId, Request $request)
    {
        $month = $request->query('month');
        $absences = $this->getAbsencesByClassroomUseCase->execute($classroomId, $month);
        return response()->json([
            'absences' => AbsenceResource::collection($absences)
        ]);
    }

    public function create(CreateAbsenceRequest $request)
    {
        $absence = $this->createAbsenceUseCase->execute($request->toDTO());
        return response()->json([
            'message' => 'Absence created successfully',
            'absence' => new AbsenceResource($absence)
        ], 201);
    }

    public function justify(int $id, JustifyAbsenceRequest $request)
    {
        // Store the file and get the path
        $path = $request->file('justification_file')->store('justifications', 'public');
        
        $this->submitAbsenceJustificationUseCase->execute($request->toDTO($id, $path));
        
        return response()->json([
            'message' => 'Justification submitted successfully'
        ]);
    }

    public function approve(int $id)
    {
        $this->approveAbsenceUseCase->execute($id);
        return response()->json([
            'message' => 'Absence approved successfully'
        ]);
    }

    public function reject(int $id)
    {
        $this->rejectAbsenceUseCase->execute($id);
        return response()->json([
            'message' => 'Absence rejected successfully'
        ]);
    }

    public function delete(int $id)
    {
        $this->deleteAbsenceUseCase->execute($id);
        return response()->json([
            'message' => 'Absence deleted successfully'
        ]);
    }
}
