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
            'tardies_count' => $report->getTardiesCount(),
            'brief_status' => $report->getBriefStatus(),
            'technical_topics' => $report->getTechnicalTopics(),
            'workshops_done' => $report->getWorkshopsDone(),
            'class_mood' => $report->getClassMood(),
            'objectives_met' => $report->isObjectivesMet(),
            'note' => $report->getNote(),
        ];

        if ($id) {
            $model = DailyReportModel::findOrFail($id);
            $model->update($data);
        } else {
            $model = DailyReportModel::create($data);
        }

        return $this->mapToEntity($model->fresh());
    }

    public function findAll(): array
    {
        return DailyReportModel::all()
            ->map(fn(DailyReportModel $model) => $this->mapToEntity($model)->toArray())
            ->values()
            ->toArray();
    }

    public function getByClassroom(int $classroomId): array
    {
        return DailyReportModel::where('classroom_id', (int)$classroomId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get()
            ->map(fn(DailyReportModel $model) => $this->mapToEntity($model)->toArray())
            ->values()
            ->toArray();
    }

    public function findById(int $id): ?DailyReportEntity
    {
        $model = DailyReportModel::find($id);
        return $model ? $this->mapToEntity($model) : null;
    }

    public function getStats(int $classroomId): array
    {
        $reports = DailyReportModel::where('classroom_id', (int)$classroomId)->get();
        if ($reports->isEmpty()) {
            return [
                'avg_absences' => 0,
                'total_reports' => 0,
                'last_report' => null
            ];
        }

        return [
            'avg_absences' => round($reports->avg('absences_count'), 1),
            'total_reports' => $reports->count(),
            'last_report' => $reports->sortByDesc('date')->first()
        ];
    }

    private function mapToEntity(DailyReportModel $model): DailyReportEntity
    {
        // Handle date casting gracefully if it's already a string or Carbon
        $date = $model->date;
        if ($date instanceof \DateTimeInterface) {
            $dateStr = $date->format('Y-m-d');
        } else {
            $dateStr = (string)$date;
            if (strlen($dateStr) > 10) $dateStr = substr($dateStr, 0, 10);
        }

        return new DailyReportEntity(
            $model->id,
            $model->formateur_id,
            $model->classroom_id,
            $dateStr,
            $model->absences_count,
            $model->tardies_count,
            $model->brief_status,
            $model->technical_topics,
            $model->workshops_done,
            $model->class_mood,
            $model->objectives_met,
            $model->note
        );
    }
}
