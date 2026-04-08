<?php

namespace App\Modules\Marketplace\Application\UseCases;

use App\Modules\Marketplace\Domain\Repositories\MarketplaceRepositoryInterface;
use App\Modules\Marketplace\Domain\Entities\OrderEntity;

/**
 * Use Case to handle the completion/validation of a marketplace order.
 * This marks the order as DELIVERED in the database.
 */
class CompleteOrderUseCase
{
    /**
     * @param MarketplaceRepositoryInterface $repository
     */
    public function __construct(
        private MarketplaceRepositoryInterface $repository
    ) {}

    /**
     * Execute the order completion logic.
     *
     * @param int $orderId
     * @return OrderEntity
     * @throws \Exception
     */
    public function execute(int $orderId): OrderEntity
    {
        $order = $this->repository->findOrderById($orderId);

        if (!$order) {
            throw new \Exception("Order not found.");
        }

        if ($order->getStatus() !== 'PENDING') {
            throw new \Exception("Only pending orders can be completed.");
        }

        // Create a new entity instance with the updated status
        $updatedOrder = new OrderEntity(
            $order->getId(),
            $order->getUserId(),
            $order->getProductId(),
            $order->getPriceAtPurchase(),
            'DELIVERED',
            $order->getCreatedAt()
        );

        return $this->repository->saveOrder($updatedOrder);
    }
}
