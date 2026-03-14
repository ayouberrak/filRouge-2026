<?php

namespace App\Modules\User\Application\UseCases;

use App\Modules\User\Domain\Repositories\UserRepositoryInterface;

class GetAllUsers
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}
 
    public function execute()
    {
        return $this->userRepository->findAll();
    }
}
