<?php

namespace App\Modules\User\Application\UseCases;

use App\Modules\User\Domain\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use App\Modules\User\Application\DTO\LoginDTO;

class LoginUser
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function execute(LoginDTO $loginDTO): array
    {
        $isValid = $this->authService->verifyCredentials($loginDTO->email, $loginDTO->password);
        if (!$isValid) {
            throw new \Exception('Invalid credentials');
        }

        $user = $this->authService->findUserByEmail($loginDTO->email);
        $token = $this->authService->createToken($user->getId(), 'auth_token');

        return [
            'user' => $user,
            'token' => $token
        ];
    }
}