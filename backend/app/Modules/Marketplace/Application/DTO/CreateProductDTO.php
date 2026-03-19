<?php

namespace App\Modules\Marketplace\Application\DTO;

class CreateProductDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly int $price,
        public readonly int $quantity,
        public readonly ?string $image = null
    ) {}
}
