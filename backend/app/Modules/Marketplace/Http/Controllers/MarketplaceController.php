<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Modules\Marketplace\Application\DTO\CreateProductDTO;
use App\Modules\Marketplace\Application\UseCases\CreateProductUseCase;
use App\Modules\Marketplace\Application\UseCases\GetAllProductsUseCase;
use App\Modules\Marketplace\Application\UseCases\PurchaseProductUseCase;
use App\Modules\Marketplace\Application\UseCases\GetAllOrdersUseCase;
use App\Modules\Marketplace\Application\UseCases\GetMyOrdersUseCase;
use App\Modules\Marketplace\Application\UseCases\CompleteOrderUseCase;
use App\Modules\Marketplace\Application\UseCases\CancelOrderUseCase;
use App\Modules\Marketplace\Application\UseCases\DeleteProductUseCase;
use App\Modules\Marketplace\Http\Requests\CreateProductRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketplaceController
{
    public function __construct(
        private CreateProductUseCase $createProductUseCase,
        private GetAllProductsUseCase $getAllProductsUseCase,
        private PurchaseProductUseCase $purchaseProductUseCase,
        private GetAllOrdersUseCase $getAllOrdersUseCase,
        private GetMyOrdersUseCase $getMyOrdersUseCase,
        private CompleteOrderUseCase $completeOrderUseCase,
        private CancelOrderUseCase $cancelOrderUseCase,
        private DeleteProductUseCase $deleteProductUseCase
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
        $validated = $request->validated();

        $dto = new CreateProductDTO(
            (string) $validated['name'],
            (string) $validated['description'],
            (int) $validated['price'],
            (int) $validated['quantity'],
            isset($validated['image']) ? (string) $validated['image'] : null
        );

        $product = $this->createProductUseCase->execute($dto);
        return response()->json(['message' => 'Produit créé', 'data' => $product->toArray()], 201);
    }

    // Admin: Complete Order
    public function completeOrder(int $id): JsonResponse
    {
        try {
            $order = $this->completeOrderUseCase->execute($id);
            return response()->json(['message' => 'Commande complétée', 'data' => $order->toArray()]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Admin: Cancel Order
    public function cancelOrder(int $id): JsonResponse
    {
        try {
            $order = $this->cancelOrderUseCase->execute($id);
            return response()->json(['message' => 'Commande annulée et points remboursés', 'data' => $order->toArray()]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Admin: Delete Product
    public function deleteProduct(int $id): JsonResponse
    {
        $this->deleteProductUseCase->execute($id);
        return response()->json(['message' => 'Produit supprimé']);
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

    // Student: My Orders
    public function myOrders(Request $request): JsonResponse
    {
        $orders = $this->getMyOrdersUseCase->execute($request->user()->id);
        return response()->json(['data' => $orders]);
    }
}
