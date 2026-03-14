<?php

namespace App\Modules\User\Application\UseCases;

use App\Modules\User\Domain\Repositories\UserRepositoryInterface;

class GetUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}
 
    public function execute(int $id)
    {
        return $this->userRepository->findById($id);
    }
}
