<?php

namespace App\Modules\Report\Application\UseCases;

use App\Modules\Report\Application\DTO\DailyReportDTO;
use App\Modules\Report\Domain\Entities\DailyReportEntity;
use App\Modules\Report\Domain\Repositories\DailyReportRepositoryInterface;

class CreateDailyReportUseCase
{
    public function __construct(
        private DailyReportRepositoryInterface $repository
    ) {}

    public function execute(DailyReportDTO $dto): DailyReportEntity
    {
        $report = new DailyReportEntity(
            null,
            $dto->formateurId,
            $dto->classroomId,
            $dto->date,
            $dto->absencesCount,
            $dto->briefStatus,
            $dto->note
        );

        return $this->repository->save($report);
    }
}
