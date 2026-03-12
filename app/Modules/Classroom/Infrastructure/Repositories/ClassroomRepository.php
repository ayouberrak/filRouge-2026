<?php

namespace App\Modules\Classroom\Infrastructure\Repositories;

use App\Modules\Classroom\Domain\Repositories\ClassroomRepositoryInterface;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;

class ClassroomRepository implements ClassroomRepositoryInterface
{
    public function findById(int $id) { return ClassroomModel::find($id); }
    public function findAll() { return ClassroomModel::all(); }
    public function create(array $data) { return ClassroomModel::create($data); }
    public function update(int $id, array $data) {
        $model = ClassroomModel::find($id);
        if ($model) {
            $model->update($data);
            return $model;
        }
        return null;
    }
    public function delete(int $id): bool {
        $model = ClassroomModel::find($id);
        return $model ? $model->delete() : false;
    }
}
