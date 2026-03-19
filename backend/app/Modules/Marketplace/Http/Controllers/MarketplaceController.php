<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Modules\Marketplace\Application\DTO\CreateProductDTO;
use App\Modules\Marketplace\Application\UseCases\CreateProductUseCase;
use App\Modules\Marketplace\Application\UseCases\GetAllProductsUseCase;
use App\Modules\Marketplace\Application\UseCases\PurchaseProductUseCase;
use App\Modules\Marketplace\Application\UseCases\GetAllOrdersUseCase;
use App\Modules\Marketplace\Http\Requests\CreateProductRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketplaceController
{
    public function __construct(
        private CreateProductUseCase $createProductUseCase,
        private GetAllProductsUseCase $getAllProductsUseCase,
        private PurchaseProductUseCase $purchaseProductUseCase,
        private GetAllOrdersUseCase $getAllOrdersUseCase
    ) {}

    // Admin: List all orders
    public function indexOrders(): JsonResponse
    {
        $orders = $this->getAllOrdersUseCase->execute();
        return response()->json(['data' => $orders]);
    }

    // Admin: Create Product
    public function storeProduct(CreateProductRequest $request): JsonResponse
    {
        $dto = new CreateProductDTO(
            $request->validated('name'),
            $request->validated('description'),
            $request->validated('price'),
            $request->validated('quantity'),
            $request->validated('image')
        );

        $product = $this->createProductUseCase->execute($dto);
        return response()->json(['message' => 'Produit créé', 'data' => $product->toArray()], 201);
    }

    // Common: List Products
    public function indexProducts(): JsonResponse
    {
        $products = $this->getAllProductsUseCase->execute();
        return response()->json(['data' => array_map(fn($p) => $p->toArray(), $products)]);
    }

    // Student: Purchase
    public function purchase(int $id): JsonResponse
    {
        try {
            $userId = auth()->id();
            $order = $this->purchaseProductUseCase->execute($userId, $id);

            return response()->json([
                'message' => 'Achat réussi !',
                'order' => $order->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
