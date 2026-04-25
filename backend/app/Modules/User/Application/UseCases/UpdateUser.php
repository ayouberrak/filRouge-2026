<?php

namespace App\Modules\User\Application\UseCases;

use App\Modules\User\Application\DTO\UpdateUserDTO;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use App\Modules\User\Domain\Entities\UserEntity;

class UpdateUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(int $userId, UpdateUserDTO $dto): ?UserEntity
    {
        $updateData = [];

        if ($dto->first_name !== null) {
            $updateData['first_name'] = $dto->first_name;
        }
        if ($dto->last_name !== null) {
            $updateData['last_name'] = $dto->last_name;
        }
        if ($dto->email !== null) {
            $updateData['email'] = $dto->email;
        }
        if ($dto->password !== null) {
            $updateData['password'] = Hash::make($dto->password);
        }
        if ($dto->role !== null) {
            $updateData['role'] = $dto->role;
        }


        if (empty($updateData)) {
            return $this->userRepository->findById($userId);
        }

        return $this->userRepository->update($userId, $updateData);
    }
}
