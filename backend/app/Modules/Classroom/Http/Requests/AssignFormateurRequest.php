<?php

namespace App\Modules\Classroom\Http\Requests;


use App\Modules\Classroom\Application\DTO\AssignFormateurDTO; 
use Illuminate\Foundation\Http\FormRequest;


class AssignFormateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'formateur_id' => 'required|integer|exists:users,id'
        ];
    }

    public function toDTO(int $classroomId): AssignFormateurDTO
    {
        return new AssignFormateurDTO(
            classroom_id: $classroomId,
            formateur_id: $this->input('formateur_id')
        );
    }
}