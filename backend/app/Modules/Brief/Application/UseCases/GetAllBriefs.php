<?php

namespace App\Modules\Brief\Application\UseCases;

use App\Modules\Brief\Domain\Repositories\BriefRepositoryInterface;

class GetAllBriefs
{
    public function __construct(
        private BriefRepositoryInterface $briefRepository
    ) {}

    public function execute(): array
    {
        return $this->briefRepository->findAll();
    }
}
