<?php

namespace App\Modules\Quiz\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeQuizResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'score' => 'required|integer|min:0|max:100',
            'ai_feedback' => 'required|string',
        ];
    }
}
