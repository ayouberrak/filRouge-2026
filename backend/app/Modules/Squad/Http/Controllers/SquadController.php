<?php

namespace App\Modules\Squad\Http\Controllers;

use App\Modules\Squad\Application\UseCases\CreateSquad;
use App\Modules\Squad\Application\UseCases\AssignMemberToSquad;
use App\Modules\Squad\Application\UseCases\RemoveMemberFromSquad;
use App\Modules\Squad\Application\UseCases\GetSquad;
use App\Modules\Squad\Application\UseCases\GetAllSquads;
use App\Modules\Squad\Http\Requests\CreateSquadRequest;
use App\Modules\Squad\Http\Requests\AssignMemberRequest;
use App\Modules\Squad\Http\Resources\SquadResource;

class SquadController
{
    public function __construct(
        private CreateSquad $createSquadUseCase,
        private AssignMemberToSquad $assignMemberToSquadUseCase,
        private RemoveMemberFromSquad $removeMemberFromSquadUseCase,
        private GetSquad $getSquadUseCase,
        private GetAllSquads $getAllSquadsUseCase
    ) {}

    public function index()
    {
        $squads = $this->getAllSquadsUseCase->execute();
        return response()->json([
            'squads' => SquadResource::collection($squads)
        ]);
    }

    public function show(int $id)
    {
        $squad = $this->getSquadUseCase->execute($id);
        if (!$squad) {
            return response()->json(['message' => 'Squad not found'], 404);
        }
        return new SquadResource($squad);
    }

    public function create(CreateSquadRequest $request)
    {
        $squad = $this->createSquadUseCase->execute($request->toDTO());
        return response()->json([
            'message' => 'Squad created successfully',
            'squad' => new SquadResource($squad)
        ], 201);
    }

    public function assignMember(int $id, AssignMemberRequest $request)
    {
        $this->assignMemberToSquadUseCase->execute($request->toDTO($id));
        return response()->json([
            'message' => 'Member assigned successfully'
        ]);
    }

    public function removeMember(int $id, int $userId)
    {
        $this->removeMemberFromSquadUseCase->execute($id, $userId);
        return response()->json([
            'message' => 'Member removed successfully'
        ]);
    }
}
