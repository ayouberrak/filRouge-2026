<?php

namespace App\Modules\Absence\Domain\Repositories;

interface AbsenceRepositoryInterface
{
    public function findById(int $id);
    public function findAll();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id): bool;

    public function findByStudentId(int $studentId): array;
    public function findByClassroomId(int $classroomId, ?string $month = null): array;
}
