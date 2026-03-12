<?php

namespace App\Modules\User\Domain\Services;
use App\Modules\User\Domain\Repositories\UserRepositoryInterface;
class AuthService
{
    private UserRepositoryInterface  $userRepository;
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function findUserByEmail(string $email)
    {
        return $this->userRepository->findByEmail($email);
    }

    public function verifyCredentials(string $email, string $password): bool
    {
        return $this->userRepository->verifyCredentials($email, $password);
    }

    public function createToken(int $userId, string $tokenName): string
    {
        return $this->userRepository->createToken($userId, $tokenName);
    }

    public function revokeToken(int $userId, string $tokenId): void
    {
        $this->userRepository->revokeToken($userId, $tokenId);
    }
}