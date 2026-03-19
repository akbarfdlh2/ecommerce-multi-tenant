<?php

namespace App\Http\Controllers;

use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(protected TenantService $tenantService) {}

    /**
     * List all active tenants (public).
     */
    public function index(): JsonResponse
    {
        $tenants = $this->tenantService->all()->map(fn ($t) => [
            '_id'    => (string) $t->_id,
            'name'   => $t->name,
            'slug'   => $t->slug,
            'domain' => $t->domain,
            'plan'   => $t->plan,
        ]);

        return response()->json(['data' => $tenants]);
    }

    /**
     * Register a new tenant (store/shop).
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'owner_email' => 'required|email',
            'domain'      => 'nullable|string|max:100',
            'plan'        => 'nullable|in:free,pro,enterprise',
        ]);

        // Check domain uniqueness
        if (!empty($data['domain'])) {
            $exists = \App\Models\Central\Tenant::where('domain', $data['domain'])->exists();
            if ($exists) {
                return response()->json(['message' => 'Domain already taken.'], 409);
            }
        }

        $tenant = $this->tenantService->create($data);

        return response()->json([
            'message' => 'Tenant registered successfully.',
            'data'    => [
                '_id'           => (string) $tenant->_id,
                'name'          => $tenant->name,
                'slug'          => $tenant->slug,
                'domain'        => $tenant->domain,
                'database_name' => $tenant->database_name,
                'plan'          => $tenant->plan,
            ],
        ], 201);
    }

    /**
     * Admin dashboard stats for current tenant.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $productCount = \App\Models\Tenant\Product::count();
        $orderCount   = \App\Models\Tenant\Order::count();
        $userCount    = \App\Models\Tenant\User::count();
        $revenue      = \App\Models\Tenant\Order::where('payment_status', 'paid')->sum('total');

        $recentOrders = \App\Models\Tenant\Order::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['order_number', 'total', 'status', 'created_at']);

        $topProducts = \App\Models\Tenant\Product::orderBy('stock', 'asc')
            ->limit(5)
            ->get(['name', 'price', 'stock', 'category']);

        return response()->json([
            'stats' => [
                'products' => $productCount,
                'orders'   => $orderCount,
                'users'    => $userCount,
                'revenue'  => $revenue,
            ],
            'recent_orders' => $recentOrders,
            'low_stock'     => $topProducts,
        ]);
    }
}
