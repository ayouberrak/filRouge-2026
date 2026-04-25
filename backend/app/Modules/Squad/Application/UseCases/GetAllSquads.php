<?php

namespace App\Modules\Squad\Application\UseCases;

use App\Modules\Squad\Domain\Repositories\SquadRepositoryInterface;

class GetAllSquads
{
    public function __construct(
        private SquadRepositoryInterface $squadRepository
    ) {}

    public function execute(?int $classroomId = null): array
    {
        if ($classroomId) {
            return $this->squadRepository->findByClassroom($classroomId);
        }
        return $this->squadRepository->findAll();
    }
}
