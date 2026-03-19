<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Services\TenantService;

class IdentifyTenant
{
    public function __construct(protected TenantService $tenantService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $strategy = config('tenancy.identification', 'header');

        $domain = match ($strategy) {
            'subdomain' => explode('.', $request->getHost())[0] ?? null,
            'domain'    => $request->getHost(),
            default     => $request->header(config('tenancy.header', 'X-Tenant-Domain')),
        };

        if (!$domain) {
            return response()->json([
                'message' => 'Tenant not identified. Please provide ' . config('tenancy.header', 'X-Tenant-Domain') . ' header.',
            ], 400);
        }

        $tenant = $this->tenantService->findByDomain($domain);

        if (!$tenant) {
            return response()->json([
                'message' => "Tenant '$domain' not found.",
            ], 404);
        }

        // ── Switch MongoDB connection to this tenant's database ─────────────
        $this->tenantService->switchDatabase($tenant);

        // ── Bind tenant to request for downstream use ──────────────────────
        $request->merge(['_tenant' => $tenant]);
        app()->instance('current_tenant', $tenant);

        return $next($request);
    }
}
