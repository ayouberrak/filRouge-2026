<?php

namespace App\Modules\Squad\Infrastructure\Repositories;

use App\Modules\Squad\Domain\Repositories\SquadRepositoryInterface;
use App\Modules\Squad\Infrastructure\Models\SquadModel;

class SquadRepository implements SquadRepositoryInterface
{
    public function findById(int $id) { return SquadModel::find($id); }
    public function findAll() { return SquadModel::all(); }
    public function create(array $data) { return SquadModel::create($data); }
    public function update(int $id, array $data) {
        $model = SquadModel::find($id);
        if ($model) {
            $model->update($data);
            return $model;
        }
        return null;
    }
    public function delete(int $id): bool {
        $model = SquadModel::find($id);
        return $model ? $model->delete() : false;
    }
}
