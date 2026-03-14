<?php

namespace App\Modules\Classroom\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Classroom\Application\DTO\CreateClassroomDTO;

class UpdateClassroomRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'formateur_id' => 'required|exists:users,id',
        ];
    }

    public function toDTO()
    {
        return new CreateClassroomDTO(
            $this->input('name'),
            $this->input('formateur_id')
        );
    }
}