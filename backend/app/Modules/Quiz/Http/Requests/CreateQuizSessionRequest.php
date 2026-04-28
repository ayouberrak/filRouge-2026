<?php

namespace App\Modules\Quiz\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuizSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => 'required|integer|exists:classrooms,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'timer_minutes' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'questions' => 'required|array|min:1',
            'questions.*.type' => 'required|string|in:multiple_choice,code,open_ended',
            'questions.*.content' => 'required|string',
            'questions.*.correct_answer' => 'nullable|string',
            'questions.*.context_data' => 'nullable',
        ];
    }
}
