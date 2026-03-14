<?php

namespace App\Modules\Squad\Application\UseCases;

use App\Modules\Squad\Domain\Repositories\SquadRepositoryInterface;

class GetAllSquads
{
    public function __construct(
        private SquadRepositoryInterface $squadRepository
    ) {}

    public function execute(): array
    {
        return $this->squadRepository->findAll();
    }
}
