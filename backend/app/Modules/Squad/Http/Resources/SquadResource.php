<?php

namespace App\Modules\Squad\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SquadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $entity = $this->resource;

        return [
            'id' => $entity->getId(),
            'name' => $entity->getName()->getValue(),
            'classroom_id' => $entity->getClassroomId(),
            'members' => $entity->getMembers(),
        ];
    }
}
