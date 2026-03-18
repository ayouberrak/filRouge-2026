<?php

namespace App\Modules\Brief\Application\UseCases;

use App\Modules\Brief\Domain\Repositories\BriefRepositoryInterface;
use App\Modules\Brief\Domain\Entities\BriefEntity;

class GetBrief
{
    public function __construct(
        private BriefRepositoryInterface $briefRepository
    ) {}

    public function execute(int $id): ?BriefEntity
    {
        return $this->briefRepository->findById($id);
    }
}
