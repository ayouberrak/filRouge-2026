<?php

namespace App\Modules\Squad\Application\UseCases;

use App\Modules\Squad\Application\DTO\AssignMemberDTO;
use App\Modules\Squad\Domain\Repositories\SquadRepositoryInterface;

class AssignMemberToSquad
{
    public function __construct(
        private SquadRepositoryInterface $squadRepository
    ) {}

    public function execute(AssignMemberDTO $dto): void
    {
        $squad = $this->squadRepository->findById($dto->squad_id);
        
        if ($squad) {
            $squad->addMember($dto->user_id);
            $this->squadRepository->assignMember($dto->squad_id, $dto->user_id);
        }
    }
}
