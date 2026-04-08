<?php

namespace App\Modules\Absence\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbsenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Modules\Absence\Domain\Entities\AbsenceEntity $entity */
        $entity = $this->resource;

        return [
            'id' => $entity->getId(),
            'student_id' => $entity->getStudentId(),
            'student' => [
                'first_name' => $entity->getStudentName(),
                'last_name' => '',
                'classroom' => [
                    'name' => $entity->getClassroomName()
                ]
            ],
            'date' => $entity->getDate(),
            'duration' => $entity->getDuration(),
            'status' => $entity->getStatus()->getValue(),
            'justification_file' => $entity->getJustificationFile() ? asset('storage/' . $entity->getJustificationFile()) : null,
        ];
    }
}
