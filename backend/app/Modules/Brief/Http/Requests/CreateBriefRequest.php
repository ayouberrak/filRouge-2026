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
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'modality' => 'nullable|in:INDIVIDUAL,GROUP',
            'status' => 'nullable|in:DRAFT,PUBLISHED,IN_PROGRESS,COMPLETED',
            'tags' => 'nullable|array',
        ];
    }
}
