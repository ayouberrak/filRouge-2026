<?php

namespace App\Modules\Marketplace\Domain\Entities;

class OrderEntity
{
    public function __construct(
        private ?int $id,
        private int $userId,
        private int $productId,
        private int $priceAtPurchase,
        private string $status,
        private ?string $createdAt = null
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getProductId(): int { return $this->productId; }
    public function getPriceAtPurchase(): int { return $this->priceAtPurchase; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'product_id' => $this->productId,
            'price_at_purchase' => $this->priceAtPurchase,
            'status' => $this->status,
            'created_at' => $this->createdAt,
        ];
    }
}
