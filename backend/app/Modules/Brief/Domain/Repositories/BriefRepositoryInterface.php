<?php

namespace App\Modules\Brief\Domain\Repositories;

interface BriefRepositoryInterface
{
    public function findById(int $id);
    public function findAll();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id): bool;
}
