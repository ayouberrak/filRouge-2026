<?php

namespace App\Modules\User\Http\Requests;

use App\Modules\User\Application\DTO\LoginDTO;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    public function toDTO()
    {
        return new LoginDTO(
            email: $this->input('email'),
            password: $this->input('password')
        );
    }
}
