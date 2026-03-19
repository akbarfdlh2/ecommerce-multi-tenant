<?php

namespace App\Models\Central;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Central tenant registry model.
 * Stored in: ecommerce_central.tenants
 *
 * @property string $_id
 * @property string $name
 * @property string $slug
 * @property string $domain
 * @property string $database_name
 * @property string $owner_email
 * @property string $plan
 * @property bool   $is_active
 * @property array  $settings
 */
class Tenant extends Model
{
    protected $connection = 'mongodb_central';
    protected $collection = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'database_name',
        'owner_email',
        'plan',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings'  => 'array',
    ];

    protected $attributes = [
        'is_active' => true,
        'plan'      => 'free',
        'settings'  => [],
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getDatabaseNameAttribute($value): string
    {
        return $value ?: config('tenancy.db_prefix') . $this->slug;
    }
}
