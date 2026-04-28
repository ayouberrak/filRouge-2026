<?php

namespace App\Modules\Livrable\Http\Requests;

use App\Modules\Livrable\Application\DTO\AddLivrableReponseDTO;
use Auth;
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
            'formateur_id' => 'nullable|exists:users,id',
            'status' => 'required|in:VALIDATED,REJECTED,VALIDE,INVALID',
            'message' => 'required|string',
        ];
    }

    public function toDTO(int $livrableId): AddLivrableReponseDTO
    {
        $statusMap = [
            'VALIDE' => 'VALIDATED',
            'VALIDATED' => 'VALIDATED',
            'INVALID' => 'REJECTED',
            'REJECTED' => 'REJECTED'
        ];

        $status = $statusMap[strtoupper($this->status)] ?? 'REJECTED';

        return new AddLivrableReponseDTO(
            $livrableId,
            $this->formateur_id ?? Auth::id(),
            $status,
            $this->message
        );
    }
}
