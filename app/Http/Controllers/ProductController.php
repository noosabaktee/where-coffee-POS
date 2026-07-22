<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\ImageStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        /** @var Outlet $outlet */
        $outlet = $request->attributes->get('current_outlet');
        $products = Product::query()->with(['category', 'outlet'])->forOutlet($outlet)->orderBy('name')->get();
        return response()->json(ProductResource::collection($products)->resolve($request));
    }

    public function store(StoreProductRequest $request, ImageStorageService $images): JsonResponse
    {
        $this->authorize('create', Product::class);
        /** @var Outlet $outlet */
        $outlet = $request->attributes->get('current_outlet');
        $data = $request->safe()->except(['image_data']);
        $data['outlet_id'] = $outlet->id;
        $data['sku'] ??= 'PRD-'.Str::upper(Str::random(8));
        $data['image_path'] = $images->storeDataUri($request->input('image_data'), 'products');
        $product = Product::query()->create($data);

        StockMovement::query()->create([
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'type' => 'initial',
            'quantity_change' => $product->stock,
            'stock_before' => 0,
            'stock_after' => $product->stock,
            'reference' => $product->sku,
            'notes' => 'Stok awal produk',
        ]);

        return response()->json([
            'message' => 'Menu berhasil ditambahkan.',
            'data' => (new ProductResource($product->load(['category', 'outlet'])))->resolve($request),
        ], 201);
    }

    public function update(UpdateProductRequest $request, Product $product, ImageStorageService $images): JsonResponse
    {
        $this->authorize('update', $product);
        $data = $request->safe()->except(['image_data', 'remove_image']);
        $beforeStock = $product->stock;

        if ($request->boolean('remove_image')) {
            $images->delete($product->image_path);
            $data['image_path'] = null;
            $data['image_url'] = null;
        } elseif ($request->filled('image_data')) {
            $data['image_path'] = $images->storeDataUri($request->input('image_data'), 'products', $product->image_path);
            $data['image_url'] = null;
        } elseif ($request->filled('image_url')) {
            $images->delete($product->image_path);
            $data['image_path'] = null;
        }

        $product->update($data);

        if ($product->stock !== $beforeStock) {
            StockMovement::query()->create([
                'outlet_id' => $product->outlet_id,
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'type' => 'adjustment',
                'quantity_change' => $product->stock - $beforeStock,
                'stock_before' => $beforeStock,
                'stock_after' => $product->stock,
                'reference' => $product->sku,
                'notes' => 'Penyesuaian stok dari halaman inventori',
            ]);
        }

        return response()->json([
            'message' => 'Menu berhasil diperbarui.',
            'data' => (new ProductResource($product->fresh()->load(['category', 'outlet'])))->resolve($request),
        ]);
    }

    public function destroy(Request $request, Product $product, ImageStorageService $images): JsonResponse
    {
        $this->authorize('delete', $product);

        if ($product->transactionItems()->exists()) {
            $product->update(['is_active' => false]);
            return response()->json(['message' => 'Produk memiliki riwayat transaksi dan telah dinonaktifkan.']);
        }

        $images->delete($product->image_path);
        $product->delete();

        return response()->json(['message' => 'Menu berhasil dihapus.']);
    }
}
