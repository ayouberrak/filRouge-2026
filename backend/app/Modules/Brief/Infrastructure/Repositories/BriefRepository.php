<?php

namespace App\Modules\Brief\Infrastructure\Repositories;

use App\Modules\Brief\Domain\Repositories\BriefRepositoryInterface;
use App\Modules\Brief\Infrastructure\Models\BriefModel;

class BriefRepository implements BriefRepositoryInterface
{
    public function findById(int $id) { return BriefModel::find($id); }
    public function findAll() { return BriefModel::all(); }
    public function create(array $data) { return BriefModel::create($data); }
    public function update(int $id, array $data) {
        $model = BriefModel::find($id);
        if ($model) {
            $model->update($data);
            return $model;
        }
        return null;
    }
    public function delete(int $id): bool {
        $model = BriefModel::find($id);
        return $model ? $model->delete() : false;
    }
}
