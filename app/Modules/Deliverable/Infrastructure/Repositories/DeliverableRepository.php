<?php

namespace App\Modules\Deliverable\Infrastructure\Repositories;

use App\Modules\Deliverable\Domain\Repositories\DeliverableRepositoryInterface;
use App\Modules\Deliverable\Infrastructure\Models\DeliverableModel;

class DeliverableRepository implements DeliverableRepositoryInterface
{
    public function findById(int $id) { return DeliverableModel::find($id); }
    public function findAll() { return DeliverableModel::all(); }
    public function create(array $data) { return DeliverableModel::create($data); }
    public function update(int $id, array $data) {
        $model = DeliverableModel::find($id);
        if ($model) {
            $model->update($data);
            return $model;
        }
        return null;
    }
    public function delete(int $id): bool {
        $model = DeliverableModel::find($id);
        return $model ? $model->delete() : false;
    }
}
