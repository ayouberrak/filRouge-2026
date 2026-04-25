<?php

namespace App\Modules\Absence\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Absence\Application\DTO\JustifyAbsenceDTO;

class JustifyAbsenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'justification_file' => 'required|file|mimes:pdf,jpg,png|max:5120',
        ];
    }

    public function toDTO(int $absenceId, string $filePath): JustifyAbsenceDTO
    {
        return new JustifyAbsenceDTO(
            absence_id: $absenceId,
            justification_file: $filePath
        );
    }
}
