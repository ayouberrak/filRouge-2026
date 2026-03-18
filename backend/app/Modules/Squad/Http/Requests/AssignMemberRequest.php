<?php

namespace App\Modules\Squad\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Squad\Application\DTO\AssignMemberDTO;

class AssignMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id'
        ];
    }

    public function toDTO(int $squadId): AssignMemberDTO
    {
        return new AssignMemberDTO(
            squad_id: $squadId,
            user_id: $this->input('user_id')
        );
    }
}
