<?php

namespace App\Modules\Absence\Infrastructure\Repositories;

use App\Modules\Absence\Domain\Repositories\AbsenceRepositoryInterface;
use App\Modules\Absence\Infrastructure\Models\AbsenceModel;
use App\Modules\Absence\Domain\Entities\AbsenceEntity;
use App\Modules\Absence\Domain\ValueObjects\AbsenceStatus;
use App\Modules\User\Infrastructure\Models\UserModel;

class AbsenceRepository implements AbsenceRepositoryInterface
{
    private function mapToDomain($model): ?AbsenceEntity
    {
        if (!$model) {
            return null;
        }

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
        $absences = [];
        foreach ($models as $model) {
            $absences[] = $this->mapToDomain($model);
        }
        return $absences;
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
        $models = AbsenceModel::with('student.classroom')
                                ->where('student_id', $studentId)
                                ->get();

        $absences = [];
        foreach ($models as $model) {
            $absences[] = $this->mapToDomain($model);
        }
        return $absences;
    }

    public function findByClassroomId(int $classroomId, ?string $month = null): array
    {
        $studentIDS = UserModel::where('classroom_id', $classroomId)
                                ->pluck('id');

        $query = AbsenceModel::whereIn('student_id', $studentIDS);

        if ($month) {
            $query->where('date', 'like', $month . '%');
        }

        $models = $query->get();

        $absences = [];
        foreach ($models as $model) {
            $absences[] = $this->mapToDomain($model);
        }
        return $absences;
    }
}
