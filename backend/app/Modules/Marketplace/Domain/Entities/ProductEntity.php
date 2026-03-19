<?php

namespace App\Modules\Marketplace\Domain\Entities;

class ProductEntity
{
    public function __construct(
        private ?int $id,
        private string $name,
        private string $description,
        private int $price,
        private int $quantity,
        private ?string $image = null
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getPrice(): int { return $this->price; }
    public function getQuantity(): int { return $this->quantity; }
    public function getImage(): ?string { return $this->image; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'image' => $this->image,
        ];
    }
}
