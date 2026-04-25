<?php

namespace App\Modules\Squad\Application\UseCases;

use App\Modules\Squad\Domain\Repositories\SquadRepositoryInterface;

class DeleteSquad
{
    public function __construct(
        private SquadRepositoryInterface $squadRepository
    ) {}

    public function execute(int $id): bool
    {
        return $this->squadRepository->delete($id);
    }
}
