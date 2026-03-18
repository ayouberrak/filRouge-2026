<?php

namespace App\Modules\Activity\Http\Controllers;

use App\Modules\Activity\Application\UseCases\CreateActivityUseCase;
use App\Modules\Activity\Http\Requests\CreateActivityRequest;
use App\Modules\Activity\Http\Requests\AssignActivityRequest;
use App\Modules\Activity\Domain\Repositories\ActivityRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActivityController
{
    public function __construct(
        private CreateActivityUseCase $createActivityUseCase,
        private ActivityRepositoryInterface $repository
    ) {}

    public function store(CreateActivityRequest $request): JsonResponse
    {
        $activity = $this->createActivityUseCase->execute($request->toDTO());

        return response()->json($activity->toArray(), 201);
    }

    public function getByClassroom(int $classroomId): JsonResponse
    {
        $activities = $this->repository->getByClassroom($classroomId);
        return response()->json($activities);
    }

    public function assign(AssignActivityRequest $request, int $id): JsonResponse
    {
        $this->repository->assignToStudents($id, $request->getStudentIds());

        return response()->json(['message' => 'Activity assigned to students successfully']);
    }

    public function getMyActivities(Request $request): JsonResponse
    {
        $activities = $this->repository->getByStudent($request->user()->id);
        return response()->json($activities);
    }
}
