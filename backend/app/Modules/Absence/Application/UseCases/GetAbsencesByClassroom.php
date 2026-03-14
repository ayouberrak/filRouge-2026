<?php

namespace App\Modules\Absence\Application\UseCases;

use App\Modules\Absence\Domain\Repositories\AbsenceRepositoryInterface;

class GetAbsencesByClassroom
{
    public function __construct(
        private AbsenceRepositoryInterface $absenceRepository
    ) {}

    public function execute(int $classroomId): array
    {
        return $this->absenceRepository->findByClassroomId($classroomId);
    }
}
