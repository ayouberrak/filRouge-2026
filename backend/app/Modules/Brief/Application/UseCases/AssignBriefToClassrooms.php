<?php

namespace App\Modules\Brief\Application\UseCases;

use App\Modules\Brief\Domain\Repositories\BriefRepositoryInterface;
use App\Modules\Brief\Domain\Entities\BriefEntity;
use Exception;

class AssignBriefToClassrooms
{
    public function __construct(
        private BriefRepositoryInterface $briefRepository
    ) {}

    public function execute(int $briefId, array $classroomIds): void
    {
        $brief = $this->briefRepository->findById($briefId);
        
        if (!$brief) {
            throw new Exception("Brief not found.");
        }

        $this->briefRepository->assignClassrooms($briefId, $classroomIds);
    }
}
