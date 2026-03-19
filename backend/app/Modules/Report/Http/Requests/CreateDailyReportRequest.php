<?php

namespace App\Modules\Report\Http\Requests;

use App\Modules\Report\Application\DTO\DailyReportDTO;
use Illuminate\Foundation\Http\FormRequest;

class CreateDailyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => 'required|exists:classrooms,id',
            'date' => 'required|date',
            'absences_count' => 'required|integer|min:0',
            'brief_status' => 'required|string|max:255',
            'note' => 'nullable|string',
        ];
    }

    public function toDTO(): DailyReportDTO
    {
        return new DailyReportDTO(
            $this->validated('classroom_id'),
            $this->validated('date'),
            $this->validated('absences_count'),
            $this->validated('brief_status'),
            $this->user()->id,
            $this->validated('note')
        );
    }
}
