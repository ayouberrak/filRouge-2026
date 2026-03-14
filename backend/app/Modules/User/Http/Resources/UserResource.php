<?php

namespace App\Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\User\Domain\Entities\AdminEntity;
use App\Modules\User\Domain\Entities\FormateurEntity;
use App\Modules\User\Domain\Entities\StudentEntity;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Modules\User\Domain\Entities\UserEntity $entity */
        $entity = $this->resource;

        $data = [
            'id' => $entity->id,
            'first_name' => $entity->first_name,
            'last_name' => $entity->last_name,
            'email' => $entity->email,
            'role' => $entity->role,
            'status' => $entity->status,
        ];

        if ($entity instanceof FormateurEntity) {
            $data['speciality'] = $entity->speciality;
        }

        if ($entity instanceof StudentEntity) {
            $data['points'] = $entity->points;
            $data['classroom_id'] = $entity->classroom_id;
            $data['squad_id'] = $entity->squad_id;
        }

        return $data;
    }
}
