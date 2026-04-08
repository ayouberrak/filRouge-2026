<?php

namespace App\Modules\Livrable\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitLivrableRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Authorize based on specific roles if needed
    }

    public function rules()
    {
        return [
            'brief_id' => 'required|exists:briefs,id',
            'student_id' => 'nullable|exists:users,id',
            'squad_id' => 'nullable|exists:squads,id',
            'link' => 'required|string|max:255',
            'message' => 'nullable|string',
        ];
    }

    public function toDTO(): \App\Modules\Livrable\Application\DTO\SubmitLivrableDTO
    {
        return new \App\Modules\Livrable\Application\DTO\SubmitLivrableDTO(
            $this->validated('brief_id'),
            $this->validated('student_id'),
            $this->validated('squad_id'),
            $this->validated('link'),
            $this->validated('message')
        );
    }
}
