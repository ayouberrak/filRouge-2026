<?php

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\User\Application\DTO\CreateUserDTO;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['admin', 'formateur', 'student'])],
            'speciality' => 'nullable|string|max:255',
        ];
    }

    public function toDTO(): CreateUserDTO
    {
        return new CreateUserDTO(
            first_name: $this->input('first_name'),
            last_name: $this->input('last_name'),
            email: $this->input('email'),
            password: $this->input('password'),
            role: $this->input('role'),
            speciality: $this->input('speciality')
        );
    }
}
