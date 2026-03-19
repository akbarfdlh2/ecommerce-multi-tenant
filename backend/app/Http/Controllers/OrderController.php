<?php

namespace App\Http\Controllers;

use App\Models\Tenant\Cart;
use App\Models\Tenant\Order;
use App\Models\Tenant\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Checkout: create order from user's cart.
     */
    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'shipping_address'         => 'required|array',
            'shipping_address.name'    => 'required|string',
            'shipping_address.phone'   => 'required|string',
            'shipping_address.address' => 'required|string',
            'shipping_address.city'    => 'required|string',
            'payment_method'           => 'required|string|in:cod,bank_transfer,ewallet',
            'notes'                    => 'nullable|string',
        ]);

        $userId = (string) $request->user()->_id;
        $cart   = Cart::where('user_id', $userId)->first();

        if (!$cart || $cart->isEmpty()) {
            return response()->json(['message' => 'Cart is empty.'], 422);
        }

        // Verify stock and build order items
        $orderItems  = [];
        $subtotal    = 0.0;
        $stockErrors = [];

        foreach ($cart->items as $item) {
            $product = Product::find($item['product_id']);

            if (!$product || !$product->isAvailable()) {
                $stockErrors[] = ($item['name'] ?? $item['product_id']) . ' is no longer available.';
                continue;
            }

            if ($product->stock < $item['quantity']) {
                $stockErrors[] = "{$product->name}: only {$product->stock} left.";
                continue;
            }

            $orderItems[] = [
                'product_id' => (string) $product->_id,
                'name'       => $product->name,
                'price'      => $product->price,
                'quantity'   => $item['quantity'],
                'subtotal'   => $item['quantity'] * $product->price,
            ];

            $subtotal += $item['quantity'] * $product->price;
        }

        if (!empty($stockErrors)) {
            return response()->json([
                'message' => 'Some items have stock issues.',
                'errors'  => $stockErrors,
            ], 422);
        }

        $shippingCost = 15000; // fixed shipping for now
        $total        = $subtotal + $shippingCost;

        // Create order
        $order = Order::create([
            'order_number'     => Order::generateOrderNumber(),
            'user_id'          => $userId,
            'items'            => $orderItems,
            'subtotal'         => $subtotal,
            'shipping_cost'    => $shippingCost,
            'total'            => $total,
            'status'           => Order::STATUS_PENDING,
            'shipping_address' => $data['shipping_address'],
            'payment_method'   => $data['payment_method'],
            'payment_status'   => Order::PAYMENT_UNPAID,
            'notes'            => $data['notes'] ?? null,
        ]);

        // Decrement stock
        foreach ($orderItems as $item) {
            Product::where('_id', $item['product_id'])->decrement('stock', $item['quantity']);
        }

        // Clear the cart
        $cart->clear();

        return response()->json([
            'message' => 'Order placed successfully.',
            'data'    => $order,
        ], 201);
    }

    /**
     * List orders for current user.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::byUser((string) $request->user()->_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    /**
     * Show single order (user must own it).
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $order = Order::byUser((string) $request->user()->_id)->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json(['data' => $order]);
    }

    /**
     * Admin: list all orders with filters.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $query = Order::query();

        if ($status = $request->query('status')) {
            $query->byStatus($status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($orders);
    }

    /**
     * Admin: update order status.
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'status'         => 'sometimes|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'sometimes|in:unpaid,paid,refunded',
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $order->update($data);

        return response()->json(['message' => 'Order updated.', 'data' => $order]);
    }
}
