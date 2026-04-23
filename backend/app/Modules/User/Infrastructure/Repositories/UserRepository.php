<?php

namespace App\Modules\User\Infrastructure\Repositories;

use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\User\Domain\Entities\UserEntity;
use App\Modules\User\Domain\Entities\AdminEntity;
use App\Modules\User\Domain\Entities\StudentEntity;
use App\Modules\User\Domain\Entities\FormateurEntity;

class UserRepository implements UserRepositoryInterface
{
    private function mapToDomain(?UserModel $model): ?UserEntity
    {
        if (!$model) return null;

        switch ($model->role) {
            case 'admin':
                return new AdminEntity(
                    $model->id,
                    $model->first_name,
                    $model->last_name,
                    $model->email,
                    $model->role,
                    $model->status
                );
            case 'formateur':
                return new FormateurEntity(
                    $model->id,
                    $model->first_name,
                    $model->last_name,
                    $model->email,
                    $model->role,
                    $model->status
                );
            case 'student':
            default:
                return new StudentEntity(
                    $model->id,
                    $model->first_name,
                    $model->last_name,
                    $model->email,
                    $model->role,
                    $model->status,
                    $model->classroom_id,
                    $model->squad_id
                );
        }
    }

    public function findById(int $id): ?UserEntity 
    { 
        return $this->mapToDomain(UserModel::find($id)); 
    }

    public function findAll(): array 
    { 
        $models = UserModel::all();
        $entities = [];
        foreach ($models as $model) {
            if ($model instanceof UserModel) {
                $entities[] = $this->mapToDomain($model);
            }
        }
        return $entities;
    }

    public function findByEmail(string $email): ?UserEntity 
    {
        return $this->mapToDomain(UserModel::where('email', $email)->first());
     }

    public function create(array $data): ?UserEntity 
    { 
        $model = UserModel::create($data);
        return $this->mapToDomain($model);
    }

    public function update(int $id, array $data): ?UserEntity 
    {
        $model = UserModel::find($id);
        if ($model) {
            $model->update($data);
            return $this->mapToDomain($model);
        }
        return null;
    }

    public function delete(int $id): bool 
    {
        $model = UserModel::find($id);
        return $model ? $model->delete() : false;
    }

    public function verifyCredentials(string $email, string $password): bool 
    {
        $model = UserModel::where('email', $email)->first();
        if (!$model) return false;
        return \Illuminate\Support\Facades\Hash::check($password, $model->password);
    }

    public function createToken(int $userId, string $tokenName): string 
    {
        $model = UserModel::find($userId);
        return $model ? $model->createToken($tokenName)->plainTextToken : '';
    }

    public function revokeToken(int $userId, string $tokenId): void
    {
        $model = UserModel::find($userId);
        if ($model) {
            $model->tokens()->where('id', $tokenId)->delete();
        }
    }
}
