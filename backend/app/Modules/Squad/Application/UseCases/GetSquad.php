<?php

namespace App\Modules\Squad\Application\UseCases;

use App\Modules\Squad\Domain\Repositories\SquadRepositoryInterface;
use App\Modules\Squad\Domain\Entities\SquadEntity;

class GetSquad
{
    public function __construct(
        private SquadRepositoryInterface $squadRepository
    ) {}

    public function execute(int $id): ?SquadEntity
    {
        return $this->squadRepository->findById($id);
    }
}
