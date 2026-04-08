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

    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $explorer = $request->query('all') === 'true' || $request->query('all') === '1';

        if ($user && $user->role === 'student' && !$explorer) {
            $briefs = $this->repository->findByClassroomId($user->classroom_id);
        } else {
            $briefs = $this->getAllBriefs->execute();
        }

        // Eager-load classrooms from Eloquent to enrich response
        $briefIds = array_map(fn($b) => $b->getId(), $briefs);
        $classroomsMap = \App\Modules\Brief\Infrastructure\Models\BriefModel::whereIn('id', $briefIds)
            ->with('classrooms:id,name')
            ->get()
            ->keyBy('id');

        $data = array_map(function ($b) use ($classroomsMap) {
            $arr = $b->toArray();
            $model = $classroomsMap->get($b->getId());
            $arr['classrooms'] = $model
                ? $model->classrooms->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toArray()
                : [];
            return $arr;
        }, $briefs);

        return response()->json(['data' => $data]);
    }


    public function show(int $id): JsonResponse
    {
        $briefModel = \App\Modules\Brief\Infrastructure\Models\BriefModel::with('formateur')->find($id);
        if (!$briefModel) return response()->json(['message' => 'Brief not found'], 404);

        $user = auth()->user();
        if ($user && $user->role === 'student') {
            $classrooms = $briefModel->classrooms->pluck('id')->toArray();
            if (!in_array($user->classroom_id, $classrooms)) {
                return response()->json(['message' => 'Unauthorized access to this brief'], 403);
            }
        }

        $briefData = $briefModel->toArray();
        if ($briefModel->formateur) {
            $briefData['formateur_name'] = $briefModel->formateur->first_name . ' ' . $briefModel->formateur->last_name;
            $briefData['formateur_avatar'] = $briefModel->formateur->avatar_url;
        }

        return response()->json(['data' => $briefData]);
    }

    public function store(CreateBriefRequest $request): JsonResponse
    {
        $dto = new BriefDTO(
            title: $request->input('title'),
            image_url: $request->input('image_url'),
            description: $request->input('description'),
            context: $request->input('context'),
            objectives: $request->input('objectives'),
            date_start: $request->input('date_start'),
            date_end: $request->input('date_end'),
            difficulty: $request->input('difficulty', 'EASY'),
            modality: $request->input('modality', 'INDIVIDUAL'),
            pedagogical_modalities: $request->input('pedagogical_modalities'),
            evaluation_modalities: $request->input('evaluation_modalities'),
            status: $request->input('status', 'DRAFT'),
            points: $request->input('points', 0),
            tags: $request->input('tags', []),
            resources: $request->input('resources', []),
            deliverables: $request->input('deliverables', []),
            performance_criteria: $request->input('performance_criteria', []),
            target_competencies: $request->input('target_competencies', []),
            file: null,
            formateur_id: auth()->id()
        );

        $brief = $this->createBrief->execute($dto);
        return response()->json(['message' => 'Brief created successfully', 'data' => $brief->toArray()], 201);
    }

    public function update(CreateBriefRequest $request, int $id): JsonResponse
    {
        try {
            $dto = new BriefDTO(
                title: $request->input('title'),
                image_url: $request->input('image_url'),
                description: $request->input('description'),
                context: $request->input('context'),
                objectives: $request->input('objectives'),
                date_start: $request->input('date_start'),
                date_end: $request->input('date_end'),
                difficulty: $request->input('difficulty', 'EASY'),
                modality: $request->input('modality', 'INDIVIDUAL'),
                pedagogical_modalities: $request->input('pedagogical_modalities'),
                evaluation_modalities: $request->input('evaluation_modalities'),
                status: $request->input('status', 'DRAFT'),
                points: $request->input('points', 0),
                tags: $request->input('tags', []),
                resources: $request->input('resources', []),
                deliverables: $request->input('deliverables', []),
                performance_criteria: $request->input('performance_criteria', []),
                target_competencies: $request->input('target_competencies', []),
                file: null,
                formateur_id: auth()->id()
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
