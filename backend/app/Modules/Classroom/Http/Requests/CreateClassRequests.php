<?php

namespace App\Modules\Classroom\Http\Requests;

use App\Modules\Classroom\Application\DTO\CreateClassroomDTO;
use Illuminate\Foundation\Http\FormRequest;


class CreateClassRequests extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'formateur_id' => 'nullable|integer|exists:users,id'
        ];
    }

    public function toDTO(): CreateClassroomDTO
    {
        return new CreateClassroomDTO(
            name: $this->input('name'),
            formateur_id: $this->input('formateur_id')
        );
    }




}