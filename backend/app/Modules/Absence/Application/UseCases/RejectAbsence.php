<?php

namespace App\Modules\Absence\Application\UseCases;

use App\Modules\Absence\Domain\Repositories\AbsenceRepositoryInterface;
use InvalidArgumentException;

class RejectAbsence
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

        $absence->reject();

        $this->absenceRepository->update($absenceId, [
            'status' => $absence->getStatus()->getValue()
        ]);
    }
}
