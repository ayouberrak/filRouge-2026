<?php

namespace App\Modules\Activity\Http\Requests;

use App\Modules\Activity\Application\DTO\CreateActivityDTO;
use Illuminate\Foundation\Http\FormRequest;

class CreateActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|string|in:live_coding,veille,workshop,quiz',
            'duration' => 'required|integer|min:1',
            'points' => 'required|integer|min:1',
            'classroom_id' => 'required|exists:classrooms,id',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:users,id',
        ];
    }

    public function toDTO(): CreateActivityDTO
    {
        return new CreateActivityDTO(
            $this->validated('title'),
            $this->validated('description'),
            $this->validated('type'),
            $this->validated('duration'),
            $this->validated('points'),
            $this->user()->id,
            $this->validated('classroom_id'),
            $this->validated('student_ids') ?? []
        );
    }
}
