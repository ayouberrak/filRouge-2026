<?php

namespace App\Modules\Absence\Application\UseCases;

use App\Modules\Absence\Application\DTO\JustifyAbsenceDTO;
use App\Modules\Absence\Domain\Repositories\AbsenceRepositoryInterface;
use InvalidArgumentException;

class SubmitAbsenceJustification
{
    public function __construct(
        private AbsenceRepositoryInterface $absenceRepository
    ) {}

    public function execute(JustifyAbsenceDTO $dto): void
    {
        $absence = $this->absenceRepository->findById($dto->absence_id);

        if (!$absence) {
            throw new InvalidArgumentException("Absence not found.");
        }

        // Apply domain logic
        $absence->submitJustification($dto->justification_file);

        // Persist
        $this->absenceRepository->update($absence->getId(), [
            'justification_file' => $absence->getJustificationFile()
        ]);
    }
}
