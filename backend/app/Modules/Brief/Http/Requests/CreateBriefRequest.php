<?php

namespace App\Modules\Brief\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBriefRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:5|max:255',
            'image_url' => 'nullable|string',
            'description' => 'required|string',
            'context' => 'nullable|string',
            'objectives' => 'nullable|array',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'difficulty' => 'nullable|in:EASY,MEDIUM,HARD',
            'modality' => 'nullable|in:INDIVIDUAL,GROUP',
            'pedagogical_modalities' => 'nullable|string',
            'evaluation_modalities' => 'nullable|string',
            'status' => 'nullable|in:DRAFT,PUBLISHED,IN_PROGRESS,COMPLETED,ARCHIVED',
            'points' => 'nullable|integer',
            'tags' => 'nullable|array',
            'resources' => 'nullable|array',
            'deliverables' => 'nullable|array',
            'performance_criteria' => 'nullable|array',
            'target_competencies' => 'nullable|array',
        ];
    }
}
