<?php

namespace App\Modules\Livrable\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddLivrableReponseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'formateur_id' => 'required|exists:users,id',
            'status' => 'required|in:validé,invalidé',
            'message' => 'required|string',
        ];
    }

    public function toDTO(int $livrableId): \App\Modules\Livrable\Application\DTO\AddLivrableReponseDTO
    {
        return new \App\Modules\Livrable\Application\DTO\AddLivrableReponseDTO(
            $livrableId,
            $this->validated('formateur_id'),
            $this->validated('status'),
            $this->validated('message')
        );
    }
}
