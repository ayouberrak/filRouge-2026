<?php

namespace App\Modules\Marketplace\Application\UseCases;

use App\Modules\Marketplace\Domain\Repositories\MarketplaceRepositoryInterface;

class GetMyOrdersUseCase
{
    public function __construct(
        private MarketplaceRepositoryInterface $repository
    ) {}

    public function execute(int $userId): array
    {
        return $this->repository->findOrdersByUserId($userId);
    }
}
