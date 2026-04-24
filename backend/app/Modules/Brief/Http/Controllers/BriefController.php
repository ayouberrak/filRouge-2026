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
            $briefs = $this->repository->findByClassroomId($user->classroom_id, $user->squad_id);
        } else {
            $briefs = $this->getAllBriefs->execute();
        }

        $data = [];

        foreach ($briefs as $brief) {

            $model = BriefModel::with(['classrooms:id,name', 'squads:id,name'])
                                ->find($brief->getId());
            $item = $brief->toArray();

            $item['classrooms'] = [];
            $item['squads'] = [];

            if ($model) {
                foreach ($model->classrooms as $classroom) {
                    $item['classrooms'][] = [
                        'id' => $classroom->id,
                        'name' => $classroom->name
                    ];
                }
                foreach ($model->squads as $squad) {
                    $item['squads'][] = [
                        'id' => $squad->id,
                        'name' => $squad->name
                    ];
                }
            }
            $data[] = $item;
        }

        return response()->json([
            'data' => $data
        ]);
    }


    public function show(int $id): JsonResponse
    {
        $user = auth()->user();

        $briefModel = BriefModel::with('formateur', 'classrooms')->find($id);

        if ($user && $user->role === 'student') {

            $autorise = false;

            foreach ($briefModel->classrooms as $classroom) {
                if ($classroom->id == $user->classroom_id) {
                    $autorise = true;
                    break;
                }
            }
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
        try {
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
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Brief store error', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Invalid request while creating brief',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function update(CreateBriefRequest $request, int $id): JsonResponse
    {
        try {
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
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Brief update error', [
                'id' => $id,
                'error' => $e->getMessage(),
                'payload' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Invalid request while updating brief',
                'error' => $e->getMessage()
            ], 400);
        }
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
