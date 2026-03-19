<?php

namespace App\Modules\Marketplace\Application\UseCases;

use App\Modules\Marketplace\Application\DTO\CreateProductDTO;
use App\Modules\Marketplace\Domain\Entities\ProductEntity;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceRepositoryInterface;

class CreateProductUseCase
{
    public function __construct(
        private MarketplaceRepositoryInterface $repository
    ) {}

    public function execute(CreateProductDTO $dto): ProductEntity
    {
        $product = new ProductEntity(
            null,
            $dto->name,
            $dto->description,
            $dto->price,
            $dto->quantity,
            $dto->image
        );

        return $this->repository->saveProduct($product);
    }
}
