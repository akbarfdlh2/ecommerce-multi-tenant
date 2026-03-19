<?php

namespace App\Http\Controllers;

use App\Models\Tenant\Cart;
use App\Models\Tenant\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get current user's cart.
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user()->_id);

        return response()->json(['data' => $cart]);
    }

    /**
     * Add an item to the cart.
     */
    public function addItem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|string',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::active()->find($data['product_id']);

        if (!$product) {
            return response()->json(['message' => 'Product not found or unavailable.'], 404);
        }

        if ($product->stock < $data['quantity']) {
            return response()->json([
                'message' => "Insufficient stock. Available: {$product->stock}",
            ], 422);
        }

        $cart = $this->getOrCreateCart($request->user()->_id);
        $cart->addItem($product, (int) $data['quantity']);

        return response()->json([
            'message' => 'Item added to cart.',
            'data'    => $cart->fresh(),
        ]);
    }

    /**
     * Update item quantity.
     */
    public function updateItem(Request $request, string $itemId): JsonResponse
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $cart = $this->getOrCreateCart($request->user()->_id);

        if (!$cart->updateItem($itemId, (int) $data['quantity'])) {
            return response()->json(['message' => 'Item not found in cart.'], 404);
        }

        return response()->json(['message' => 'Cart updated.', 'data' => $cart->fresh()]);
    }

    /**
     * Remove a specific item from the cart.
     */
    public function removeItem(Request $request, string $itemId): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user()->_id);
        $cart->removeItem($itemId);

        return response()->json(['message' => 'Item removed.', 'data' => $cart->fresh()]);
    }

    /**
     * Clear entire cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user()->_id);
        $cart->clear();

        return response()->json(['message' => 'Cart cleared.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function getOrCreateCart(string $userId): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $userId],
            ['items' => [], 'total' => 0.0]
        );
    }
}
