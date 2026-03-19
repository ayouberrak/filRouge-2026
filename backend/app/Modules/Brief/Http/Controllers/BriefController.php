<?php

namespace App\Modules\Brief\Http\Controllers;

use App\Modules\Brief\Application\DTO\BriefDTO;
use App\Modules\Brief\Application\UseCases\CreateBrief;
use App\Modules\Brief\Application\UseCases\UpdateBrief;
use App\Modules\Brief\Application\UseCases\GetBrief;
use App\Modules\Brief\Application\UseCases\GetAllBriefs;
use App\Modules\Brief\Application\UseCases\AssignBriefToClassrooms;
use App\Modules\Brief\Domain\Repositories\BriefRepositoryInterface;
use App\Modules\Brief\Domain\ValueObjects\BriefTitle;
use App\Modules\Brief\Domain\ValueObjects\BriefDatePeriod;
use App\Modules\Brief\Domain\ValueObjects\DifficultyLevel;
use App\Modules\Brief\Domain\ValueObjects\BriefModality;
use App\Modules\Brief\Domain\ValueObjects\BriefStatus;
use App\Modules\Brief\Http\Requests\CreateBriefRequest;
use App\Modules\Brief\Http\Requests\AssignClassroomsRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BriefController
{
    public function __construct(
        private CreateBrief $createBrief,
        private UpdateBrief $updateBrief,
        private GetBrief $getBrief,
        private GetAllBriefs $getAllBriefs,
        private AssignBriefToClassrooms $assignBriefToClassrooms,
        private BriefRepositoryInterface $repository
    ) {}

    public function index(): JsonResponse
    {
        $user = auth()->user();
        
        if ($user && $user->role === 'student') {
            $briefs = $this->repository->findByClassroomId($user->classroom_id);
        } else {
            $briefs = $this->getAllBriefs->execute();
        }

        return response()->json(['data' => array_map(fn($b) => $b->toArray(), $briefs)]);
    }

    public function show(int $id): JsonResponse
    {
        $brief = $this->getBrief->execute($id);
        if (!$brief) return response()->json(['message' => 'Brief not found'], 404);

        $user = auth()->user();
        if ($user && $user->role === 'student') {
            $classrooms = \App\Modules\Brief\Infrastructure\Models\BriefModel::find($id)->classrooms->pluck('id')->toArray();
            if (!in_array($user->classroom_id, $classrooms)) {
                return response()->json(['message' => 'Unauthorized access to this brief'], 403);
            }
        }

        return response()->json(['data' => $brief->toArray()]);
    }

    public function store(CreateBriefRequest $request): JsonResponse
    {
        $dto = new BriefDTO(
            title: $request->input('title'),
            description: $request->input('description'),
            objectives: $request->input('objectives'),
            date_start: $request->input('date_start'),
            date_end: $request->input('date_end'),
            difficulty: $request->input('difficulty', 'EASY'),
            modality: $request->input('modality', 'INDIVIDUAL'),
            status: $request->input('status', 'DRAFT'),
            tags: $request->input('tags', []),
            resources: $request->input('resources', []),
            file: null, // Custom handling for file upload if necessary
            formateur_id: auth()->id() // Taken securely from the token
        );

        $brief = $this->createBrief->execute($dto);
        return response()->json(['message' => 'Brief created successfully', 'data' => $brief->toArray()], 201);
    }

    public function update(CreateBriefRequest $request, int $id): JsonResponse
    {
        try {
            $dto = new BriefDTO(
                title: $request->input('title'),
                description: $request->input('description'),
                objectives: $request->input('objectives'),
                date_start: $request->input('date_start'),
                date_end: $request->input('date_end'),
                difficulty: $request->input('difficulty', 'EASY'),
                modality: $request->input('modality', 'INDIVIDUAL'),
                status: $request->input('status', 'DRAFT'),
                tags: $request->input('tags', []),
                resources: $request->input('resources', []),
                file: null,
                formateur_id: auth()->id() // Optional updates based on context
            );

            $brief = $this->updateBrief->execute($id, $dto);
            return response()->json(['message' => 'Brief updated successfully', 'data' => $brief->toArray()]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function assignClassrooms(AssignClassroomsRequest $request, int $id): JsonResponse
    {
        try {
            $classroomIds = $request->input('classroom_ids');
            $this->assignBriefToClassrooms->execute($id, $classroomIds);
            
            return response()->json([
                'message' => 'Classrooms assigned successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
