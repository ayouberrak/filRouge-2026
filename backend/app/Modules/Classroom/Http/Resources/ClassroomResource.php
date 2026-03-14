<?php

namespace App\Modules\Classroom\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $entity = $this->resource;

        return [
            'id' => $entity->getId(),
            'name' => $entity->getName()->getValue(),
            'formateur_id' => $entity->getFormateurId(),
            'created_at' => null,
        ];
    }
}
