<?php

namespace App\Modules\Activity\Http\Controllers;

use App\Modules\Activity\Application\UseCases\CreateActivityUseCase;
use App\Modules\Activity\Http\Requests\CreateActivityRequest;
use App\Modules\Activity\Http\Requests\AssignActivityRequest;
use App\Modules\Activity\Domain\Repositories\ActivityRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ActivityController
{
    public function __construct(
        private CreateActivityUseCase $createActivityUseCase,
        private ActivityRepositoryInterface $repository
    ) {}

    public function store(CreateActivityRequest $request): JsonResponse
    {
        $activity = $this->createActivityUseCase->execute($request->toDTO());

        return response()->json(['data' => $activity->toArray()], 201);
    }

    public function getByClassroom(int $classroomId): JsonResponse
    {
        Log::info("ActivityController: Fetching for classroom ID: " . $classroomId);
        
        $user = auth()->user();
        // Allow Coach/Admin or the Student if they belong to this classroom
        $isCoach = in_array($user->role, ['formateur', 'admin', 'coach']);
        $isStudentInClass = ($user->role === 'student' && $user->classroom_id == $classroomId);

        if (!$isCoach && !$isStudentInClass) {
            return response()->json(['error' => 'Accès non autorisé à cette classe'], 403);
        }

        $activities = $this->repository->getByClassroom($classroomId);
        Log::info("ActivityController: Found " . count($activities) . " activities");

        return response()->json(['data' => $activities]);
    }

    public function assign(AssignActivityRequest $request, int $id): JsonResponse
    {
        $this->repository->assignToStudents($id, $request->getStudentIds());

        return response()->json(['message' => 'Activity assigned to students successfully']);
    }

    public function assignClassroom(Request $request, int $id): JsonResponse
    {
        $classroomId = $request->input('classroom_id');
        if (!$classroomId) {
            return response()->json(['error' => 'Classroom ID is required'], 400);
        }

        $this->repository->assignToClassroom($id, $classroomId);

        return response()->json(['message' => 'Activity assigned to classroom successfully']);
    }

    public function getMyActivities(Request $request): JsonResponse
    {
        $activities = $this->repository->getByStudent($request->user()->id);
        return response()->json(['data' => $activities]);
    }
}
