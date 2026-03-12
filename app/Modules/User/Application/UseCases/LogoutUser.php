<?php

namespace App\Modules\User\Application\UseCases;

use App\Modules\User\Domain\Services\AuthService;

class LogoutUser
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function execute(int $userId, string $tokenId): void
    {
        $this->authService->revokeToken($userId, $tokenId);
    }
}
