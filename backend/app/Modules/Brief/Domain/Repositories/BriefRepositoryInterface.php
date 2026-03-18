<?php

namespace App\Modules\Brief\Domain\Repositories;

use App\Modules\Brief\Domain\Entities\BriefEntity;

interface BriefRepositoryInterface
{
    public function save(BriefEntity $brief): BriefEntity;

    public function findById(int $id): ?BriefEntity;
    
    public function findByClassroomId(int $classroomId): array;

    public function findAll(): array;
    
    public function delete(int $id): bool;

    public function assignClassrooms(int $briefId, array $classroomIds): void;
}
