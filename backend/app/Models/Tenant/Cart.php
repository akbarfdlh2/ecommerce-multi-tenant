<?php

namespace App\Models\Tenant;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Shopping cart model, one per user.
 * Stored in: tenant_{slug}.carts
 *
 * @property string $_id
 * @property string $user_id
 * @property array  $items   [ { product_id, name, price, quantity, subtotal } ]
 * @property float  $total
 */
class Cart extends Model
{
    protected $connection = 'mongodb_tenant';
    protected $collection = 'carts';

    protected $fillable = [
        'user_id',
        'items',
        'total',
    ];

    protected $casts = [
        'total' => 'float',
    ];

    protected $attributes = [
        'items' => [],
        'total' => 0.0,
    ];

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function recalculateTotal(): void
    {
        $total = collect($this->items)->sum(fn ($item) => $item['subtotal'] ?? 0);
        $this->total = $total;
        $this->save();
    }

    public function addItem(Product $product, int $qty): void
    {
        $items = collect($this->items ?? []);
        $pid   = (string) $product->_id;

        $existingIndex = $items->search(fn ($i) => (string) ($i['product_id'] ?? '') === $pid);

        if ($existingIndex !== false) {
            $items[$existingIndex]['quantity'] += $qty;
            $items[$existingIndex]['subtotal']  = $items[$existingIndex]['quantity'] * $product->price;
        } else {
            $items->push([
                'product_id' => $pid,
                'name'       => $product->name,
                'price'      => $product->price,
                'image'      => $product->images[0] ?? null,
                'quantity'   => $qty,
                'subtotal'   => $qty * $product->price,
            ]);
        }

        $this->items = $items->values()->toArray();
        $this->recalculateTotal();
    }

    public function updateItem(string $productId, int $qty): bool
    {
        $items = collect($this->items ?? []);
        $index = $items->search(fn ($i) => (string) ($i['product_id'] ?? '') === $productId);

        if ($index === false) {
            return false;
        }

        if ($qty <= 0) {
            $items->forget($index);
        } else {
            $items[$index]['quantity'] = $qty;
            $items[$index]['subtotal'] = $qty * $items[$index]['price'];
        }

        $this->items = $items->values()->toArray();
        $this->recalculateTotal();
        return true;
    }

    public function removeItem(string $productId): void
    {
        $items       = collect($this->items ?? []);
        $this->items = $items->reject(fn ($i) => (string) ($i['product_id'] ?? '') === $productId)->values()->toArray();
        $this->recalculateTotal();
    }

    public function clear(): void
    {
        $this->items = [];
        $this->total = 0.0;
        $this->save();
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}
