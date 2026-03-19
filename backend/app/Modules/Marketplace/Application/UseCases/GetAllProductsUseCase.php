<?php

namespace App\Modules\Marketplace\Application\UseCases;

use App\Modules\Marketplace\Domain\Repositories\MarketplaceRepositoryInterface;

class GetAllProductsUseCase
{
    public function __construct(
        private MarketplaceRepositoryInterface $repository
    ) {}

    public function execute(): array
    {
        return $this->repository->findAllProducts();
    }
}
