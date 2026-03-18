<?php

namespace App\Modules\User\Application\UseCases;

use App\Modules\User\Application\DTO\CreateUserDTO;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use App\Modules\User\Domain\Entities\UserEntity;

class CreateUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(CreateUserDTO $dto): ?UserEntity
    {
        $userData = [
            'first_name' => $dto->first_name,
            'last_name' => $dto->last_name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'role' => $dto->role,
            'status' => 'active', 
        ];

        if ($dto->role === 'formateur' && $dto->speciality) {
            $userData['speciality'] = $dto->speciality;
        }

        return $this->userRepository->create($userData);
    }
}
