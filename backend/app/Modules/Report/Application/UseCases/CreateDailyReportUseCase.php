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
            $dto->tardiesCount,
            $dto->briefStatus,
            $dto->technicalTopics,
            $dto->workshopsDone,
            $dto->classMood,
            $dto->objectivesMet,
            $dto->note
        );

        $savedReport = $this->repository->save($report);

        // Dispatch notification event
        \App\Modules\Report\Domain\Events\DailyReportSubmitted::dispatch($savedReport->getId());

        return $savedReport;
    }
}
