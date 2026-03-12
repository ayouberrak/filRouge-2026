<?php

namespace App\Modules\Deliverable\Domain\Repositories;

interface DeliverableRepositoryInterface
{
    public function findById(int $id);
    public function findAll();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id): bool;
}
