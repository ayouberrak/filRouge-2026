<?php

namespace App\Modules\Activity\Domain\Repositories;

use App\Modules\Activity\Domain\Entities\ActivityEntity;

interface ActivityRepositoryInterface
{
    public function save(ActivityEntity $activity): ActivityEntity;
    public function findById(int $id): ?ActivityEntity;
    public function getByClassroom(int $classroomId): array;
    public function getByFormateur(int $formateurId): array;
    public function getByStudent(int $studentId): array;
    public function assignToStudents(int $activityId, array $studentIds): void;
    public function assignToClassroom(int $activityId, int $classroomId): void;
}
