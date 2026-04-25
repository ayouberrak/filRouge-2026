<?php

namespace App\Modules\Absence\Application\UseCases;

use App\Modules\Absence\Domain\Repositories\AbsenceRepositoryInterface;

class DeleteAbsence
{
    public function __construct(
        private AbsenceRepositoryInterface $absenceRepository
    ) {}

    public function execute(int $id): bool
    {
        return $this->absenceRepository->delete($id);
    }
}
