<?php

namespace App\Modules\Marketplace\Application\UseCases;

use App\Modules\Marketplace\Domain\Repositories\MarketplaceRepositoryInterface;

class DeleteProductUseCase
{
    public function __construct(
        private MarketplaceRepositoryInterface $repository
    ) {}

    public function execute(int $productId): bool
    {
        return $this->repository->deleteProduct($productId);
    }
}
