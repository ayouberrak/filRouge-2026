<?php

namespace App\Modules\Squad\Infrastructure\Repositories;

use App\Modules\Squad\Domain\Repositories\SquadRepositoryInterface;
use App\Modules\Squad\Infrastructure\Models\SquadModel;
use App\Modules\Squad\Domain\Entities\SquadEntity;
use App\Modules\Squad\Domain\ValueObjects\SquadName;

class SquadRepository implements SquadRepositoryInterface
{
    private function mapToDomain(?SquadModel $model): ?SquadEntity
    {
        if (!$model) return null;

        $members = $model->members()->pluck('id')->toArray();

        return new SquadEntity(
            $model->id,
            new SquadName($model->name),
            $model->classroom_id,
            $members
        );
    }

    public function findById(int $id): ?SquadEntity 
    { 
        return $this->mapToDomain(SquadModel::with('members')->find($id)); 
    }

    public function findAll(): array 
    { 
        $models = SquadModel::with('members')->get();
        $entities = [];
        foreach ($models as $model) {
            if ($model instanceof SquadModel) {
                $entities[] = $this->mapToDomain($model);
            }
        }
        return $entities;
    }

    public function create(array $data): ?SquadEntity 
    { 
        $model = SquadModel::create($data);
        return $this->mapToDomain($model); 
    }

    public function update(int $id, array $data): ?SquadEntity 
    {
        $model = SquadModel::find($id);
        if ($model) {
            $model->update($data);
            return $this->mapToDomain($model);
        }
        return null;
    }

    public function delete(int $id): bool 
    {
        $model = SquadModel::find($id);
        return $model ? $model->delete() : false;
    }

    // Specific repository methods for members
    public function assignMember(int $squadId, int $userId): void
    {
        $model = SquadModel::find($squadId);
        if ($model) {
            // Because relationships are on UserModel, we might have to update the UserModel's squad_id
            // Equivalent to finding user and updating squad_id
            \App\Modules\User\Infrastructure\Models\UserModel::where('id', $userId)
                ->update(['squad_id' => $squadId]);
        }
    }

    public function removeMember(int $squadId, int $userId): void
    {
        $model = SquadModel::find($squadId);
        if ($model) {
            \App\Modules\User\Infrastructure\Models\UserModel::where('id', $userId)
                ->where('squad_id', $squadId)
                ->update(['squad_id' => null]);
        }
    }
}
