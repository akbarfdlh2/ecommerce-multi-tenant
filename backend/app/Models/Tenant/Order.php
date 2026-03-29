<?php

namespace App\Models\Tenant;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Order model, stored per tenant.
 * Stored in: tenant_{slug}.orders
 *
 * @property string $_id
 * @property string $order_number
 * @property string $user_id
 * @property array  $items
 * @property float  $subtotal
 * @property float  $shipping_cost
 * @property float  $total
 * @property string $status   (pending | processing | shipped | delivered | cancelled)
 * @property array  $shipping_address
 * @property string $payment_method
 * @property string $payment_status  (unpaid | paid | refunded)
 * @property string $notes
 */
class Order extends Model
{
    protected $connection = 'mongodb_tenant';
    protected $collection = 'orders';

    const STATUS_PENDING    = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED    = 'shipped';
    const STATUS_DELIVERED  = 'delivered';
    const STATUS_CANCELLED  = 'cancelled';

    const PAYMENT_UNPAID   = 'unpaid';
    const PAYMENT_PAID     = 'paid';
    const PAYMENT_REFUNDED = 'refunded';

    protected $fillable = [
        'order_number',
        'user_id',
        'items',
        'subtotal',
        'shipping_cost',
        'total',
        'status',
        'shipping_address',
        'payment_method',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'subtotal'     => 'float',
        'shipping_cost' => 'float',
        'total'        => 'float',
    ];

    protected $attributes = [
        'status'         => self::STATUS_PENDING,
        'payment_status' => self::STATUS_PENDING,
        'shipping_cost'  => 0.0,
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }
}
