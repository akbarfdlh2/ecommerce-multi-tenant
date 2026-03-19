<?php

namespace App\Models\Tenant;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

/**
 * Tenant-scoped User model.
 * Stored in: tenant_{slug}.users
 *
 * @property string $_id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role   (admin | customer)
 * @property bool   $is_active
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb_tenant';
    protected $collection = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'phone',
        'address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    protected $attributes = [
        'role'      => 'customer',
        'is_active' => true,
    ];

    // ── Mutators ──────────────────────────────────────────────────────────────

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
