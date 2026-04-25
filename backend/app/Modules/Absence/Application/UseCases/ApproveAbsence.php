<?php

namespace App\Modules\Absence\Application\UseCases;

use App\Modules\Absence\Domain\Repositories\AbsenceRepositoryInterface;
use InvalidArgumentException;

class ApproveAbsence
{
    public function __construct(
        private AbsenceRepositoryInterface $absenceRepository
    ) {}

    public function execute(int $absenceId): void
    {
        $absence = $this->absenceRepository->findById($absenceId);

        if (!$absence) {
            throw new InvalidArgumentException("absence not found.");
        }

        $absence->approve();

        $this->absenceRepository->update($absenceId, [
            'status' => $absence->getStatus()->getValue()
        ]);
    }
}
