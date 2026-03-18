<?php

namespace App\Modules\User\Application\UseCases;

use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use App\Modules\User\Domain\Entities\UserEntity;

class BanUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(int $userId): ?UserEntity
    {
        return $this->userRepository->update($userId, ['status' => 'banned']);
    }
}
