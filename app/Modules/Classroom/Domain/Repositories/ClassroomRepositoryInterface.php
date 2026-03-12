<?php

namespace App\Modules\Classroom\Domain\Repositories;

interface ClassroomRepositoryInterface
{
    public function findById(int $id);
    public function findAll();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id): bool;
}
