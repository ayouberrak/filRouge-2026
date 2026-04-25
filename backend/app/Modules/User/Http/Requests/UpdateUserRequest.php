<?php

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\User\Application\DTO\UpdateUserDTO;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'password' => 'sometimes|string|min:6',
            'role' => ['sometimes', Rule::in(['admin', 'formateur', 'student'])],
        ];
    }

    public function toDTO(): UpdateUserDTO
    {
        return new UpdateUserDTO(
            first_name: $this->input('first_name'),
            last_name: $this->input('last_name'),
            email: $this->input('email'),
            password: $this->input('password'),
            role: $this->input('role')
        );
    }
}
