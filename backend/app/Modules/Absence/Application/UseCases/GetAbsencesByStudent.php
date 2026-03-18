<?php

namespace App\Modules\Absence\Application\UseCases;

use App\Modules\Absence\Domain\Repositories\AbsenceRepositoryInterface;

class GetAbsencesByStudent
{
    public function __construct(
        private AbsenceRepositoryInterface $absenceRepository
    ) {}

    public function execute(int $studentId): array
    {
        return $this->absenceRepository->findByStudentId($studentId);
    }
}
