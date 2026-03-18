<?php

namespace App\Modules\Brief\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBriefRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Autorisaton gérée par le middleware
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string',
            'objectives' => 'nullable|string',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'difficulty' => 'nullable|in:EASY,MEDIUM,HARD',
            'modality' => 'nullable|in:INDIVIDUAL,GROUP',
            'status' => 'nullable|in:DRAFT,PUBLISHED,IN_PROGRESS,COMPLETED,ARCHIVED',
            'tags' => 'nullable|array',
            'resources' => 'nullable|array',
        ];
    }
}
