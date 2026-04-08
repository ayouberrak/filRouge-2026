<?php

namespace App\Modules\Marketplace\Infrastructure\Repositories;

use App\Modules\Marketplace\Domain\Entities\OrderEntity;
use App\Modules\Marketplace\Domain\Entities\ProductEntity;
use App\Modules\Marketplace\Domain\Repositories\MarketplaceRepositoryInterface;
use App\Modules\Marketplace\Infrastructure\Models\OrderModel;
use App\Modules\Marketplace\Infrastructure\Models\ProductModel;

class MarketplaceRepository implements MarketplaceRepositoryInterface
{
    // Products
    public function saveProduct(ProductEntity $product): ProductEntity
    {
        $id = $product->getId();
        $data = [
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'quantity' => $product->getQuantity(),
            'image' => $product->getImage(),
        ];

        if ($id) {
            $model = ProductModel::find($id);
            if ($model) {
                $model->update($data);
            }
        } else {
            $model = ProductModel::create($data);
        }

        return $this->mapToProductEntity($model);
    }

    public function findAllProducts(): array
    {
        return ProductModel::all()
            ->map(fn(ProductModel $model) => $this->mapToProductEntity($model))
            ->toArray();
    }

    public function findProductById(int $id): ?ProductEntity
    {
        $model = ProductModel::find($id);
        return $model ? $this->mapToProductEntity($model) : null;
    }

    public function deleteProduct(int $id): bool
    {
        $model = ProductModel::find($id);
        return $model ? $model->delete() : false;
    }

    // Orders
    public function saveOrder(OrderEntity $order): OrderEntity
    {
        $id = $order->getId();
        $data = [
            'user_id' => $order->getUserId(),
            'product_id' => $order->getProductId(),
            'price_at_purchase' => $order->getPriceAtPurchase(),
            'status' => $order->getStatus(),
        ];

        if ($id) {
            $model = OrderModel::find($id);
            if ($model) {
                $model->update($data);
            }
        } else {
            $model = OrderModel::create($data);
        }

        return $this->mapToOrderEntity($model);
    }

    public function findAllOrders(): array
    {
        return OrderModel::with(['user', 'product'])->orderByDesc('created_at')->get()
            ->map(function (OrderModel $model) {
                $entity = $this->mapToOrderEntity($model);
                return array_merge($entity->toArray(), [
                    'user' => $model->user ? [
                        'first_name' => $model->user->first_name,
                        'last_name' => $model->user->last_name,
                    ] : null,
                    'product' => $model->product ? [
                        'name' => $model->product->name,
                    ] : null,
                ]);
            })
            ->toArray();
    }

    public function findOrderById(int $id): ?OrderEntity
    {
        $model = OrderModel::find($id);
        return $model ? $this->mapToOrderEntity($model) : null;
    }

    public function findOrdersByUserId(int $userId): array
    {
        return OrderModel::with('product')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (OrderModel $model) {
                $entity = $this->mapToOrderEntity($model);
                return array_merge($entity->toArray(), [
                    'product_name'  => $model->product?->name,
                    'product_image' => $model->product?->image,
                ]);
            })
            ->toArray();
    }

    private function mapToProductEntity(ProductModel $model): ProductEntity
    {
        return new ProductEntity(
            $model->id,
            $model->name,
            $model->description,
            $model->price,
            $model->quantity,
            $model->image
        );
    }

    private function mapToOrderEntity(OrderModel $model): OrderEntity
    {
        return new OrderEntity(
            $model->id,
            $model->user_id,
            $model->product_id,
            $model->price_at_purchase,
            $model->status,
            $model->created_at->toDateTimeString()
        );
    }
}
