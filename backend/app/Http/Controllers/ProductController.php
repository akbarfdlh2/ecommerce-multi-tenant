<?php

namespace App\Http\Controllers;

use App\Models\Tenant\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * List products with optional filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::active();

        if ($category = $request->query('category')) {
            $query->byCategory($category);
        }

        if ($request->boolean('in_stock')) {
            $query->inStock();
        }

        if ($minPrice = $request->query('min_price')) {
            $query->where('price', '>=', (float) $minPrice);
        }

        if ($maxPrice = $request->query('max_price')) {
            $query->where('price', '<=', (float) $maxPrice);
        }

        $perPage  = min((int) $request->query('per_page', 12), 50);
        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($products);
    }

    /**
     * Search products by name or description.
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = $request->validate(['q' => 'required|string|min:2'])['q'];

        $products = Product::active()
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                  ->orWhere('description', 'like', "%$keyword%");
            })
            ->limit(20)
            ->get();

        return response()->json(['data' => $products, 'keyword' => $keyword]);
    }

    /**
     * Get a single product.
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::active()->find($id)
            ?? Product::where('slug', $id)->where('is_active', true)->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json(['data' => $product]);
    }

    /**
     * Create a new product.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category'    => 'required|string|max:100',
            'sku'         => 'nullable|string|max:100',
            'weight'      => 'nullable|numeric|min:0',
            'attributes'  => 'nullable|array',
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['name']);

        $product = Product::create($data);

        return response()->json([
            'message' => 'Product created.',
            'data'    => $product,
        ], 201);
    }

    /**
     * Update an existing product.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $data = $request->validate([
            'name'        => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'category'    => 'sometimes|string|max:100',
            'sku'         => 'nullable|string|max:100',
            'weight'      => 'nullable|numeric|min:0',
            'attributes'  => 'nullable|array',
            'is_active'   => 'sometimes|boolean',
        ]);

        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $id);
        }

        $product->update($data);

        return response()->json(['message' => 'Product updated.', 'data' => $product]);
    }

    /**
     * Soft-delete a product (set is_active = false).
     */
    public function destroy(string $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $product->update(['is_active' => false]);

        return response()->json(['message' => 'Product deleted.']);
    }

    /**
     * Upload product image (stores base64 URL in images array).
     */
    public function uploadImage(Request $request, string $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $request->validate(['image' => 'required|image|max:2048']);

        $path   = $request->file('image')->store('products', 'public');
        $images = $product->images ?? [];
        array_unshift($images, '/storage/' . $path);

        $product->update(['images' => $images]);

        return response()->json(['message' => 'Image uploaded.', 'images' => $images]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function generateUniqueSlug(string $name, ?string $exceptId = null): string
    {
        $base  = Str::slug($name);
        $slug  = $base;
        $count = 1;

        while (true) {
            $query = Product::where('slug', $slug);
            if ($exceptId) {
                $query->where('_id', '!=', $exceptId);
            }
            if (!$query->exists()) break;
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }
}
