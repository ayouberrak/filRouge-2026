<?php

namespace App\Modules\Quiz\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuizSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Add authorization logic if needed, e.g., only formateurs can create
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'brief_id' => 'required|integer|exists:briefs,id',
            'timer_minutes' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'questions' => 'required|array|min:1',
            'questions.*.type' => 'required|string|in:multiple_choice,code,open_ended',
            'questions.*.content' => 'required|string',
            'questions.*.correct_answer' => 'nullable|string',
            'questions.*.context_data' => 'nullable',
            'questions.*.points' => 'nullable|integer|min:1',
        ];
    }
}
