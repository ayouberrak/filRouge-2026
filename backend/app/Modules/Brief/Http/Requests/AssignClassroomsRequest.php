<?php

namespace App\Modules\Brief\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignClassroomsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_ids' => 'required|array|min:1',
            'classroom_ids.*' => 'exists:classrooms,id',
        ];
    }
}
