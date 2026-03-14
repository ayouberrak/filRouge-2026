<?php

namespace App\Modules\Classroom\Domain\Repositories;

interface ClassroomRepositoryInterface
{
    public function findById(int $id);
    public function findAll();
    public function create($data);
    public function update(int $id, $data);
    public function delete(int $id);

    public function assignFormateur(int $classroomId, int $formateurId);
}
