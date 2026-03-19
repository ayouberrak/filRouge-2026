<?php

namespace App\Modules\Report\Infrastructure\Repositories;

use App\Modules\Report\Domain\Entities\DailyReportEntity;
use App\Modules\Report\Domain\Repositories\DailyReportRepositoryInterface;
use App\Modules\Report\Infrastructure\Models\DailyReportModel;

class DailyReportRepository implements DailyReportRepositoryInterface
{
    public function save(DailyReportEntity $report): DailyReportEntity
    {
        $id = $report->getId();
        $data = [
            'formateur_id' => $report->getFormateurId(),
            'classroom_id' => $report->getClassroomId(),
            'date' => $report->getDate(),
            'absences_count' => $report->getAbsencesCount(),
            'brief_status' => $report->getBriefStatus(),
            'note' => $report->getNote(),
        ];

        $model = DailyReportModel::updateOrCreate(['id' => $id], $data);

        return $this->mapToEntity($model);
    }

    public function findAll(): array
    {
        return DailyReportModel::all()
            ->map(fn(DailyReportModel $model) => $this->mapToEntity($model))
            ->toArray();
    }

    public function getByClassroom(int $classroomId): array
    {
        return DailyReportModel::where('classroom_id', $classroomId)
            ->get()
            ->map(fn(DailyReportModel $model) => $this->mapToEntity($model))
            ->toArray();
    }

    public function findById(int $id): ?DailyReportEntity
    {
        $model = DailyReportModel::find($id);
        return $model ? $this->mapToEntity($model) : null;
    }

    private function mapToEntity(DailyReportModel $model): DailyReportEntity
    {
        return new DailyReportEntity(
            $model->id,
            $model->formateur_id,
            $model->classroom_id,
            $model->date,
            $model->absences_count,
            $model->brief_status,
            $model->note
        );
    }
}
