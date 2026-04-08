<?php

namespace App\Modules\Absence\Infrastructure\Repositories;

use App\Modules\Absence\Domain\Repositories\AbsenceRepositoryInterface;
use App\Modules\Absence\Infrastructure\Models\AbsenceModel;
use App\Modules\Absence\Domain\Entities\AbsenceEntity;
use App\Modules\Absence\Domain\ValueObjects\AbsenceStatus;

class AbsenceRepository implements AbsenceRepositoryInterface
{
    private function mapToDomain(?AbsenceModel $model): ?AbsenceEntity
    {
        if (!$model) return null;

        return new AbsenceEntity(
            $model->id,
            $model->student_id,
            $model->date,
            $model->duration,
            new AbsenceStatus($model->status),
            $model->justification_file,
            $model->student ? ($model->student->first_name . ' ' . $model->student->last_name) : null,
            $model->student?->classroom?->name
        );
    }

    public function findById(int $id): ?AbsenceEntity
    { 
        return $this->mapToDomain(AbsenceModel::find($id)); 
    }

    public function findAll(): array
    { 
        $models = AbsenceModel::with('student.classroom')->get();
        $entities = [];
        foreach ($models as $model) {
            if ($model instanceof AbsenceModel) {
                $entities[] = $this->mapToDomain($model);
            }
        }
        return $entities;
    }

    public function create(array $data): ?AbsenceEntity
    { 
            $model = AbsenceModel::create($data);
            return $this->mapToDomain($model); 
    }

    public function update(int $id, array $data): ?AbsenceEntity
    {
        $model = AbsenceModel::find($id);
        if ($model) {
            $model->update($data);
            return $this->mapToDomain($model);
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $model = AbsenceModel::find($id);
        return $model ? $model->delete() : false;
    }

    public function findByStudentId(int $studentId): array
    {
        $models = AbsenceModel::with('student.classroom')->where('student_id', $studentId)->get();
        $entities = [];
        foreach ($models as $model) {
            if ($model instanceof AbsenceModel) {
                $entities[] = $this->mapToDomain($model);
            }
        }
        return $entities;
    }

    public function findByClassroomId(int $classroomId, ?string $month = null): array
    {
        $query = AbsenceModel::whereHas('student', function ($query) use ($classroomId) {
            $query->where('classroom_id', $classroomId);
        });

        if ($month) {
            $query->where('date', 'like', $month . '%');
        }

        $models = $query->get();

        $entities = [];
        foreach ($models as $model) {
            if ($model instanceof AbsenceModel) {
                $entities[] = $this->mapToDomain($model);
            }
        }
        return $entities;
    }
}
