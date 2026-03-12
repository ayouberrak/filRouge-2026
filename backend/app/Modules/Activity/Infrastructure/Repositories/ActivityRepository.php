<?php

namespace App\Modules\Activity\Infrastructure\Repositories;

use App\Modules\Activity\Domain\Repositories\ActivityRepositoryInterface;
use App\Modules\Activity\Infrastructure\Models\ActivityModel;

class ActivityRepository implements ActivityRepositoryInterface
{
    public function findById(int $id) { return ActivityModel::find($id); }
    public function findAll() { return ActivityModel::all(); }
    public function create(array $data) { return ActivityModel::create($data); }
    public function update(int $id, array $data) {
        $model = ActivityModel::find($id);
        if ($model) {
            $model->update($data);
            return $model;
        }
        return null;
    }
    public function delete(int $id): bool {
        $model = ActivityModel::find($id);
        return $model ? $model->delete() : false;
    }
}
