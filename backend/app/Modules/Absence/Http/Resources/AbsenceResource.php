<?php

namespace App\Modules\Absence\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AbsenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
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
            'justification_file' => $entity->getJustificationFile()
                                ? Storage::disk('public')->url($entity->getJustificationFile()) 
                                : null,
        ];
    }
}
