<?php

namespace App\Modules\Livrable\Http\Requests;

use App\Modules\Livrable\Application\DTO\AddLivrableReponseDTO;
use Illuminate\Support\Facades\Auth;
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
            'message' => 'nullable|string',
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

        $rawStatus = strtoupper($this->input('status', ''));
        $status = $statusMap[$rawStatus] ?? 'REJECTED';
        $defaultMsg = $status === 'VALIDATED' ? 'Travail validé.' : 'Travail à revoir.';

        return new AddLivrableReponseDTO(
            $livrableId,
            $this->formateur_id ?? Auth::id(),
            $status,
            $this->message ?? $defaultMsg
        );
    }
}
