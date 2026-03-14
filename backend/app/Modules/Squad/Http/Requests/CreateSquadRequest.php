<?php

namespace App\Modules\Squad\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Squad\Application\DTO\CreateSquadDTO;

class CreateSquadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'classroom_id' => 'required|integer|exists:classrooms,id',
            'members' => 'nullable|array',
            'members.*' => 'integer|exists:users,id'
        ];
    }

    public function toDTO(): CreateSquadDTO
    {
        return new CreateSquadDTO(
            name: $this->input('name'),
            classroom_id: $this->input('classroom_id'),
            members: $this->input('members', [])
        );
    }
}
