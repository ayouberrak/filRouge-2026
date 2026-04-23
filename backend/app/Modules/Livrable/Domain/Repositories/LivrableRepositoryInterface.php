<?php

namespace App\Modules\Livrable\Domain\Repositories;

use App\Modules\Livrable\Domain\Entities\LivrableEntity;


interface LivrableRepositoryInterface
{
    public function save(LivrableEntity $livrable): LivrableEntity;
    public function findById(int $id): ?LivrableEntity;

    public function findByBriefId(int $briefId): array;
}
