<?php

namespace App\Modules\Classroom\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Classroom\Application\UseCases\CreateClassroom;
use App\Modules\Classroom\Application\UseCases\DeleteClassroom;
use App\Modules\Classroom\Application\UseCases\GetClassroom;
use App\Modules\Classroom\Application\UseCases\AssignFormateur;
use App\Modules\Classroom\Application\UseCases\GetAllClassrooms;
use App\Modules\Classroom\Application\UseCases\UpdateClassroom;
use App\Modules\Classroom\Http\Requests\CreateClassRequests;
use App\Modules\Classroom\Http\Requests\AssignFormateurRequest;
use App\Modules\Classroom\Http\Resources\ClassroomResource;
use App\Modules\Classroom\Http\Requests\UpdateClassroomRequest;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Http\Request;



class ClassroomController
{
    public function __construct(
        private CreateClassroom $createClassroomUseCase,
        private GetClassroom $getClassroomUseCase,
        private UpdateClassroom $updateClassroomUseCase,
        private GetAllClassrooms $getAllClassroomsUseCase, 
        private DeleteClassroom $deleteClassroomUseCase,
        private AssignFormateur $assignFormateurUseCase
    ) {}

    public function index()
    {
        $classrooms = ClassroomModel::with(['formateur'])
            ->withCount(['students']) // as in sql
            ->get();

        return response()->json(['data' => $classrooms]);
    }



    public function create(CreateClassRequests $request)
    {
        $classroomDTO = $request->toDTO();
        $this->createClassroomUseCase->execute($classroomDTO);


        return response()->json([
            'message' => 'Classroom created successfully'
            ], 201);
    }

    public function show(int $id)
    {
        $classroom = $this->getClassroomUseCase->execute($id);
        if (!$classroom) {
            return response()->json([
                'message' => 'class invalid'
            ], 404);
        }
        return new ClassroomResource($classroom);
    }

    public function delete(int $id)
    {
        $this->deleteClassroomUseCase->execute($id);
        return response()->json([
            'message' => 'Classroom deleted successfully'
        ]);
    }

    public function assignFormateur(AssignFormateurRequest $request, int $id)
    {
        $dto = $request->toDTO($id);

        $this->assignFormateurUseCase->execute($dto);
        return response()->json(['
            message' => 'Formateur assigned successfully'
        ]);
    }

    public function update(int $id, UpdateClassroomRequest $request)
    {
        $classroomDTO = $request->toDTO();
        $this->updateClassroomUseCase->execute($id, $classroomDTO);
        return response()->json([
            'message' => 'Classroom updated successfully'
        ]);
    }

    public function assignStudents(Request $request, int $id)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id'
        ]);

        UserModel::whereIn('id', $request->student_ids)
            ->update(['classroom_id' => $id]);

        return response()->json([
            'message' => 'Students assigned successfully'
        ]);
    }

    public function myClassrooms()
    {
        $formateurId = auth()->id();
        $classrooms = ClassroomModel::where('formateur_id', $formateurId)
            ->withCount('students')
            ->get()
            ->map(fn($c) => [ // resource
                'id'             => $c->id,
                'name'           => $c->name,
                'students_count' => $c->students_count,
            ]);

        return response()->json(['data' => $classrooms]);
    }
}