<?php

namespace App\Modules\Squad\Domain\Repositories;

interface SquadRepositoryInterface
{
    public function findById(int $id);
    public function findAll();
    public function findByClassroom(int $classroomId): array;
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id): bool;

    public function assignMember(int $squadId, int $userId): void;
    public function removeMember(int $squadId, int $userId): void;
}
