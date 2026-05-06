<?php

namespace App\Modules\Brief\Http\Controllers;

use App\Modules\Brief\Application\DTO\BriefDTO;
use App\Modules\Brief\Application\UseCases\CreateBrief;
use App\Modules\Brief\Application\UseCases\UpdateBrief;
use App\Modules\Brief\Application\UseCases\GetBrief;
use App\Modules\Brief\Application\UseCases\GetAllBriefs;
use App\Modules\Brief\Application\UseCases\AssignBriefToClassrooms;
use App\Modules\Brief\Application\UseCases\AssignBriefToSquads;
use App\Modules\Brief\Domain\Repositories\BriefRepositoryInterface;
use App\Modules\Brief\Domain\ValueObjects\BriefDatePeriod;
use App\Modules\Brief\Domain\ValueObjects\BriefModality;
use App\Modules\Brief\Domain\ValueObjects\BriefStatus;
use App\Modules\Brief\Http\Requests\CreateBriefRequest;
use App\Modules\Brief\Http\Requests\AssignClassroomsRequest;
use App\Modules\Brief\Infrastructure\Models\BriefModel;
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
        private AssignBriefToSquads $assignBriefToSquads,
        private BriefRepositoryInterface $repository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $explorer = $request->query('all') === 'true' || $request->query('all') === '1';

        if ($user && $user->role === 'student' && !$explorer) {
            $briefEntities = $this->repository->findByClassroomId($user->classroom_id, $user->squad_id);
        } else if ($user && $user->role === 'formateur' && !$explorer) {
            $briefEntities = $this->repository->findByFormateurId($user->id);
        } else {
            $briefEntities = $this->getAllBriefs->execute();
        }

        $briefIds = array_map(fn($b) => $b->getId(), $briefEntities);

        // 2. Chargement groupé des modèles avec relations pour éviter le N+1
        $models = BriefModel::with(['classroom', 'squads', 'formateur'])
            ->withCount('quizSessions')
            ->whereIn('id', $briefIds)
            ->get()
            ->keyBy('id');

        $data = [];
        foreach ($briefEntities as $entity) {
            $model = $models->get($entity->getId());
            if (!$model) continue;

            $item = $entity->toArray();
            
            $item['classrooms'] = [];
            if ($model->classroom) {
                $item['classrooms'][] = [
                    'id' => $model->classroom->id,
                    'name' => $model->classroom->name
                ];
            }

            $item['squads'] = [];
            foreach ($model->squads as $squad) {
                $item['squads'][] = [
                    'id' => $squad->id,
                    'name' => $squad->name
                ];
            }

            if ($model->formateur) {
                $item['formateur_name'] = $model->formateur->first_name . ' ' . $model->formateur->last_name;
            }

            $item['has_quiz'] = $model->quiz_sessions_count > 0;
            
            $data[] = $item;
        }

        return response()->json([
            'data' => $data
        ]);
    }


    public function show(int $id): JsonResponse
    {
        $user = auth()->user();

        $briefModel = BriefModel::with(['formateur', 'classroom'])->find($id);

        if ($user && $user->role === 'student') {
            $autorise = ($briefModel->classroom_id == $user->classroom_id);
        }

        $data = $briefModel->toArray();

        if ($briefModel->formateur) {
            $data['formateur_name'] =
                $briefModel->formateur->first_name . ' ' . $briefModel->formateur->last_name;

            $data['formateur_avatar'] = $briefModel->formateur->avatar_url;
        }

        return response()->json([
            'data' => $data
        ]);
    }

    public function store(CreateBriefRequest $request): JsonResponse
    {
            $dto = new BriefDTO(
                title: $request->input('title'),
                image_url: $request->input('image_url'),
                description: $request->input('description'),
                context: $request->input('context'),
                date_start: $request->input('date_start'),
                date_end: $request->input('date_end'),
                modality: $request->input('modality', 'INDIVIDUAL'),
                status: $request->input('status', 'DRAFT'),
                tags: $request->input('tags', []),
                file: null,
                formateur_id: auth()->id()
            );

            $brief = $this->createBrief->execute($dto);
            return response()->json([
                'message' => 'Brief created',
                'data' => $brief->toArray()],
                201
            );
    }

    public function update(CreateBriefRequest $request, int $id): JsonResponse
    {
            $dto = new BriefDTO(
                title: $request->input('title'),
                image_url: $request->input('image_url'),
                description: $request->input('description'),
                context: $request->input('context'),
                date_start: $request->input('date_start'),
                date_end: $request->input('date_end'),
                modality: $request->input('modality', 'INDIVIDUAL'),
                status: $request->input('status', 'DRAFT'),
                tags: $request->input('tags', []),
                file: null,
                formateur_id: auth()->id()
            );

            $brief = $this->updateBrief->execute($id, $dto);
            return response()->json([
                'message' => 'Brief updated successfully',
                'data' => $brief->toArray()
            ]);
    }
    

    public function assignClassrooms(AssignClassroomsRequest $request, int $id): JsonResponse
    {
            $classroomIds = $request->input('classroom_ids');
            $this->assignBriefToClassrooms->execute($id, $classroomIds);
            
            return response()->json([
                'message' => 'Classrooms assigned successfully'
            ]);
    }

    public function assignSquads(Request $request, int $id): JsonResponse
    {
            $squadIds = $request->input('squad_ids');
            $this->assignBriefToSquads->execute($id, $squadIds);
            
            return response()->json([
                'message' => 'Squads assigned successfully'
            ]);
    }
}
