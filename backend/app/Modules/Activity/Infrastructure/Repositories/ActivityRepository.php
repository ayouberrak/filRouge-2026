<?php

namespace App\Modules\Activity\Infrastructure\Repositories;

use App\Modules\Activity\Domain\Entities\ActivityEntity;
use App\Modules\Activity\Domain\Repositories\ActivityRepositoryInterface;
use App\Modules\Activity\Domain\ValueObjects\ActivityType;
use App\Modules\Activity\Infrastructure\Models\ActivityModel;
use App\Modules\User\Infrastructure\Models\UserModel;

class ActivityRepository implements ActivityRepositoryInterface
{
    public function save(ActivityEntity $activity): ActivityEntity
    {
        $id = $activity->getId();
        $modelData = [
            'title' => $activity->getTitle(),
            'description' => $activity->getDescription(),
            'activity_type' => $activity->getType()->getValue(),
            'duration' => $activity->getDuration(),
            'points' => $activity->getPoints(),
            'formateur_id' => $activity->getFormateurId(),
            'classroom_id' => $activity->getClassroomId(),
        ];

        $model = ActivityModel::updateOrCreate(['id' => $id], $modelData);

        return $this->mapToEntity($model);
    }

    public function findById(int $id): ?ActivityEntity
    {
        $model = ActivityModel::find($id);
        return $model ? $this->mapToEntity($model) : null;
    }

    public function getByClassroom(int $classroomId): array
    {
        $models = ActivityModel::where('classroom_id', $classroomId)->get();

        return $models->map(fn(ActivityModel $m) => $this->mapToEntity($m))->toArray();
    }

    public function getByStudent(int $studentId): array
    {
        $models = ActivityModel::whereHas('students', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })->get();

        return $models->map(fn(ActivityModel $m) => $this->mapToEntity($m))->toArray();
    }

    public function assignToStudents(int $activityId, array $studentIds): void
    {
        $activity = ActivityModel::findOrFail($activityId);
        $activity->students()->syncWithoutDetaching($studentIds);
    }

    private function mapToEntity(ActivityModel $model): ActivityEntity
    {
        return new ActivityEntity(
            $model->id,
            $model->title,
            $model->description,
            new ActivityType((string)$model->activity_type),
            (int)$model->duration,
            (int)$model->points,
            $model->formateur_id,
            $model->classroom_id
        );
    }
}
