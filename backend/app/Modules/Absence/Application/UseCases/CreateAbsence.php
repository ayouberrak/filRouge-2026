<?php

namespace App\Modules\Absence\Application\UseCases;

use App\Modules\Absence\Application\DTO\CreateAbsenceDTO;
use App\Modules\Absence\Domain\Repositories\AbsenceRepositoryInterface;
use App\Modules\Absence\Domain\Entities\AbsenceEntity;
use App\Modules\Absence\Domain\ValueObjects\AbsenceStatus;

class CreateAbsence
{
    public function __construct(
        private AbsenceRepositoryInterface $absenceRepository
    ) {}

    public function execute(CreateAbsenceDTO $dto): ?AbsenceEntity
    {
        $absenceData = [
            'student_id' => $dto->student_id,
            'date' => $dto->date,
            'duration' => $dto->duration,
            'status' => AbsenceStatus::PENDING
        ];

        return $this->absenceRepository->create($absenceData);
    }
}
