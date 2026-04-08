<?php

namespace App\Modules\Marketplace\Application\UseCases;

use App\Modules\Marketplace\Domain\Repositories\MarketplaceRepositoryInterface;
use App\Modules\Marketplace\Domain\Entities\OrderEntity;
use App\Modules\Marketplace\Domain\Entities\ProductEntity;
use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Support\Facades\DB;

/**
 * Use Case to handle the cancellation of a marketplace order.
 * This refunds points to the student and adds stock back to the product.
 * Performed within a database transaction for data integrity.
 */
class CancelOrderUseCase
{
    /**
     * @param MarketplaceRepositoryInterface $repository
     * @param \App\Modules\User\Domain\Repositories\UserRepositoryInterface $userRepository
     */
    public function __construct(
        private MarketplaceRepositoryInterface $repository,
        private \App\Modules\User\Domain\Repositories\UserRepositoryInterface $userRepository
    ) {}

    /**
     * Execute the cancellation and refund logic.
     *
     * @param int $orderId
     * @return OrderEntity
     * @throws \Exception
     */
    public function execute(int $orderId): OrderEntity
    {
        return DB::transaction(function () use ($orderId) {
            $order = $this->repository->findOrderById($orderId);

            if (!$order) {
                throw new \Exception("Order not found.");
            }

            if ($order->getStatus() !== 'PENDING') {
                throw new \Exception("Only pending orders can be canceled.");
            }

            // 1. Refund points to user
            $user = $this->userRepository->findById($order->getUserId());
            if ($user) {
                $user->addPoints($order->getPriceAtPurchase());
                $this->userRepository->updateBalance($user->getId(), $user->getPoints());
            }

            // 2. Increment product stock
            $product = $this->repository->findProductById($order->getProductId());
            if ($product) {
                $product->incrementStock();
                $this->repository->saveProduct($product);
            }

            // 3. Mark order as CANCELLED
            $updatedOrder = new OrderEntity(
                $order->getId(),
                $order->getUserId(),
                $order->getProductId(),
                $order->getPriceAtPurchase(),
                'CANCELLED',
                $order->getCreatedAt()
            );

            return $this->repository->saveOrder($updatedOrder);
        });
    }
}
