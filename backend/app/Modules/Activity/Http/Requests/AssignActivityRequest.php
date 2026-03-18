<?php

namespace App\Modules\Activity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ];
    }

    public function getStudentIds(): array
    {
        return $this->validated('student_ids');
    }
}
