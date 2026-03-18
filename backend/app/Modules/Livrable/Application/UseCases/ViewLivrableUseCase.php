<?php

namespace App\Modules\Livrable\Application\UseCases;

use App\Modules\Livrable\Domain\Entities\LivrableEntity;
use App\Modules\Livrable\Domain\Repositories\LivrableRepositoryInterface;

class ViewLivrableUseCase
{
    private LivrableRepositoryInterface $repository;

    public function __construct(LivrableRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): ?LivrableEntity
    {
        return $this->repository->findById($id);
    }
}
