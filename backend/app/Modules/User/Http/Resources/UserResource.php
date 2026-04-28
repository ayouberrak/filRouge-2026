<?php

namespace App\Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\User\Domain\Entities\AdminEntity;
use App\Modules\User\Domain\Entities\FormateurEntity;
use App\Modules\User\Domain\Entities\StudentEntity;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $entity = $this->resource;

        $data = [
            'id' => $entity->getId(),
            'first_name' => $entity->getFirstName(),
            'last_name' => $entity->getLastName(),
            'email' => $entity->getEmail(),
            'role' => $entity->getRole(),
            'status' => $entity->getStatus(),
        ];

        if ($entity instanceof FormateurEntity) {
        }

        if ($entity instanceof StudentEntity) {
            $data['classroom_id'] = $entity->getClassroomId();
            $data['squad_id'] = $entity->getSquadId();
        }

        return $data;
    }
}
