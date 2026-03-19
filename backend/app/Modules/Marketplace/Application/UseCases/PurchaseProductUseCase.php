<?php

namespace App\Modules\Marketplace\Application\UseCases;

use App\Modules\Marketplace\Domain\Entities\OrderEntity;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceRepositoryInterface;
use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Support\Facades\DB;

class PurchaseProductUseCase
{
    public function __construct(
        private MarketplaceRepositoryInterface $repository
    ) {}

    public function execute(int $userId, int $productId): OrderEntity
    {
        return DB::transaction(function () use ($userId, $productId) {
            $product = $this->repository->findProductById($productId);
            if (!$product) throw new \Exception("Produit non trouvé");
            if ($product->getQuantity() <= 0) throw new \Exception("Produit en rupture de stock");

            $user = UserModel::findOrFail($userId);
            if ($user->total_points < $product->getPrice()) {
                throw new \Exception("Points insuffisants. Vous avez {$user->total_points} points, mais le produit coûte {$product->getPrice()}.");
            }

            // Deduct points
            $user->total_points -= $product->getPrice();
            $user->save();

            // Decrease product quantity
            $newProduct = new \App\Modules\Marketplace\Domain\Entities\ProductEntity(
                $product->getId(),
                $product->getName(),
                $product->getDescription(),
                $product->getPrice(),
                $product->getQuantity() - 1,
                $product->getImage()
            );
            $this->repository->saveProduct($newProduct);

            // Create Order
            $order = new OrderEntity(
                null,
                $userId,
                $productId,
                $product->getPrice(),
                'PENDING'
            );

            return $this->repository->saveOrder($order);
        });
    }
}
