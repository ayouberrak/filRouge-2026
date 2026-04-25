<?php

namespace App\Modules\Brief\Domain\Repositories;

use App\Modules\Brief\Domain\Entities\BriefEntity;

interface BriefRepositoryInterface
{
    public function save(BriefEntity $brief): BriefEntity;

    public function findById(int $id): ?BriefEntity;
    
    public function findByClassroomId(int $classroomId, ?int $squadId = null): array;

    public function findByClassroomIds(array $classroomIds): array;

    public function findByFormateurId(int $formateurId): array;

    public function findAll(): array;
    
    public function delete(int $id): bool;

    public function assignClassrooms(int $briefId, array $classroomIds): void;
    
    public function assignSquads(int $briefId, array $squadIds): void;
}
