<?php

namespace App\Modules\Squad\Application\UseCases;

use App\Modules\Squad\Domain\Repositories\SquadRepositoryInterface;

class RemoveMemberFromSquad
{
    public function __construct(
        private SquadRepositoryInterface $squadRepository
    ) {}

    public function execute(int $squadId, int $userId): void
    {
        $squad = $this->squadRepository->findById($squadId);
        
        if ($squad) {
            $squad->removeMember($userId);
            $this->squadRepository->removeMember($squadId, $userId);
        }
    }
}
