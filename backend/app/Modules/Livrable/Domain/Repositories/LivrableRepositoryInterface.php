<?php

namespace App\Modules\Livrable\Domain\Repositories;

use App\Modules\Livrable\Domain\Entities\LivrableEntity;
use App\Modules\Livrable\Domain\Entities\ReponseLivrableEntity;

interface LivrableRepositoryInterface
{
    public function save(LivrableEntity $livrable): LivrableEntity;
    public function findById(int $id): ?LivrableEntity;
    public function saveResponse(ReponseLivrableEntity $reponse): ReponseLivrableEntity;
    public function findResponseById(int $id): ?ReponseLivrableEntity;
    public function findByBriefId(int $briefId): array;
}
