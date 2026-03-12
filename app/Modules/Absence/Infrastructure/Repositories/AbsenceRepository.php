<?php

namespace App\Modules\Absence\Infrastructure\Repositories;

use App\Modules\Absence\Domain\Repositories\AbsenceRepositoryInterface;
use App\Modules\Absence\Infrastructure\Models\AbsenceModel;

class AbsenceRepository implements AbsenceRepositoryInterface
{
    public function findById(int $id) { return AbsenceModel::find($id); }
    public function findAll() { return AbsenceModel::all(); }
    public function create(array $data) { return AbsenceModel::create($data); }
    public function update(int $id, array $data) {
        $model = AbsenceModel::find($id);
        if ($model) {
            $model->update($data);
            return $model;
        }
        return null;
    }
    public function delete(int $id): bool {
        $model = AbsenceModel::find($id);
        return $model ? $model->delete() : false;
    }
}
