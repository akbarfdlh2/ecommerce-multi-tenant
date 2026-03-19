<?php

namespace App\Services;

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Handles tenant lookup and dynamic database switching.
 */
class TenantService
{
    public function __construct(protected TenantDatabaseManager $dbManager) {}

    // ── Lookup ────────────────────────────────────────────────────────────────

    public function findByDomain(string $domain): ?Tenant
    {
        $cacheKey = 'tenant_domain_' . md5($domain);
        $ttl      = config('tenancy.cache_ttl', 300);

        return Cache::remember($cacheKey, $ttl, function () use ($domain) {
            return Tenant::where('domain', $domain)
                         ->where('is_active', true)
                         ->first();
        });
    }

    public function findBySlug(string $slug): ?Tenant
    {
        return Cache::remember('tenant_slug_' . $slug, config('tenancy.cache_ttl', 300), function () use ($slug) {
            return Tenant::where('slug', $slug)->where('is_active', true)->first();
        });
    }

    public function findById(string $id): ?Tenant
    {
        return Tenant::find($id);
    }

    // ── Switch database ───────────────────────────────────────────────────────

    public function switchDatabase(Tenant $tenant): void
    {
        $this->dbManager->switchTo($tenant->database_name);
    }

    // ── Create new tenant ─────────────────────────────────────────────────────

    public function create(array $data): Tenant
    {
        $slug         = Str::slug($data['name']);
        $uniqueSlug   = $this->ensureUniqueSlug($slug);
        $databaseName = config('tenancy.db_prefix') . $uniqueSlug;
        $domain       = $data['domain'] ?? $uniqueSlug . '.localhost';

        $tenant = Tenant::create([
            'name'          => $data['name'],
            'slug'          => $uniqueSlug,
            'domain'        => $domain,
            'database_name' => $databaseName,
            'owner_email'   => $data['owner_email'],
            'plan'          => $data['plan'] ?? 'free',
            'settings'      => $data['settings'] ?? [],
        ]);

        // Initialize the tenant's MongoDB database with indexes
        $this->dbManager->initialize($tenant);

        return $tenant;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function ensureUniqueSlug(string $base): string
    {
        $slug  = $base;
        $count = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }

    public function all()
    {
        return Tenant::active()->get();
    }
}
