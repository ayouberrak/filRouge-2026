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
            'scheduled_at' => $activity->getScheduledAt(),
            'duration' => $activity->getDuration(),
            'duration_minutes' => $activity->getDurationMinutes(),
            'points' => $activity->getPoints(),
            'formateur_id' => $activity->getFormateurId(),
            'classroom_id' => $activity->getClassroomId(),
            'objectives' => $activity->getObjectives(),
            'context' => $activity->getContext(),
            'exploration_points' => $activity->getExplorationPoints(),
            'work_rule' => $activity->getWorkRule(),
            'resources' => $activity->getResources(),
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
        $models = ActivityModel::with('students')
            ->where('classroom_id', $classroomId)
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return $models->map(fn(ActivityModel $m) => $this->mapToEntity($m))->toArray();
    }

    public function getByStudent(int $studentId): array
    {
        $models = ActivityModel::with('students')
            ->whereHas('students', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return $models->map(fn(ActivityModel $m) => $this->mapToEntity($m))->toArray();
    }

    public function assignToStudents(int $activityId, array $studentIds): void
    {
        $activity = ActivityModel::findOrFail($activityId);
        $activity->students()->syncWithoutDetaching($studentIds);
    }

    public function assignToClassroom(int $activityId, int $classroomId): void
    {
        $studentIds = UserModel::where('classroom_id', $classroomId)
            ->where('role', 'student')
            ->pluck('id')
            ->toArray();

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
            (string)$model->duration,
            (int)($model->duration_minutes ?? 60),
            (int)$model->points,
            $model->formateur_id,
            $model->classroom_id,
            $model->scheduled_at ? $model->scheduled_at->toDateTimeString() : null,
            $model->objectives,
            $model->context,
            $model->exploration_points,
            $model->work_rule,
            $model->resources,
            $model->students ? $model->students->toArray() : []
        );
    }
}
