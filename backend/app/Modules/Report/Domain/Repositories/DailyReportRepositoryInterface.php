<?php

namespace App\Modules\Report\Domain\Repositories;

use App\Modules\Report\Domain\Entities\DailyReportEntity;

interface DailyReportRepositoryInterface
{
    public function save(DailyReportEntity $report): DailyReportEntity;
    public function findAll(): array;
    public function getByClassroom(int $classroomId): array;
    public function findById(int $id): ?DailyReportEntity;
}
