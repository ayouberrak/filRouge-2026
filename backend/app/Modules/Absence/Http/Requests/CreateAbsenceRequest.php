<?php

namespace App\Modules\Absence\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Absence\Application\DTO\CreateAbsenceDTO;

class CreateAbsenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|integer|exists:users,id',
            'date' => 'required|date',
            'duration' => 'required|integer|min:1'
        ];
    }

    public function toDTO(): CreateAbsenceDTO
    {
        return new CreateAbsenceDTO(
            student_id: $this->input('student_id'),
            date: $this->input('date'),
            duration: $this->input('duration')
        );
    }
}
