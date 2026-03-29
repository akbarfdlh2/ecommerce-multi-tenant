<?php

namespace App\Models\Tenant;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Product model, stored per tenant.
 * Stored in: tenant_{slug}.products
 *
 * @property string $_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property float  $price
 * @property int    $stock
 * @property string $category
 * @property array  $images
 * @property array  $attributes
 * @property bool   $is_active
 */
class Product extends Model
{
    protected $connection = 'mongodb_tenant';
    protected $collection = 'products';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'category',
        'images',
        'attributes',
        'is_active',
        'sku',
        'weight',
    ];

    protected $casts = [
        'price'     => 'float',
        'stock'     => 'integer',
        'weight'    => 'float',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active'  => true,
        'stock'      => 0,
        'images'     => [],
        'attributes' => [],
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    public function decrementStock(int $qty): void
    {
        $this->decrement('stock', $qty);
    }
}
