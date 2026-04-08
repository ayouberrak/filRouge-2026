<?php

namespace App\Modules\Marketplace\Domain\Repositories;

use App\Modules\Marketplace\Domain\Entities\ProductEntity;
use App\Modules\Marketplace\Domain\Entities\OrderEntity;

interface MarketplaceRepositoryInterface
{
    // Products
    public function saveProduct(ProductEntity $product): ProductEntity;
    public function findAllProducts(): array;
    public function findProductById(int $id): ?ProductEntity;
    public function deleteProduct(int $id): bool;

    // Orders
    public function saveOrder(OrderEntity $order): OrderEntity;
    public function findAllOrders(): array;
    public function findOrderById(int $id): ?OrderEntity;
    public function findOrdersByUserId(int $userId): array;
}
