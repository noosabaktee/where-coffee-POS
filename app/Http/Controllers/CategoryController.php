<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Category::class);
        $categories = Category::query()->orderBy('sort_order')->orderBy('name')->get();
        return response()->json(CategoryResource::collection($categories)->resolve($request));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);
        $data = $request->validated();
        $data['code'] ??= 'CAT-'.Str::upper(Str::random(6));
        $category = Category::query()->create($data);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan.',
            'data' => (new CategoryResource($category))->resolve($request),
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);
        $category->update($request->validated());

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data' => (new CategoryResource($category->fresh()))->resolve($request),
        ]);
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {
        $this->authorize('delete', $category);
        if ($category->products()->exists()) {
            throw ValidationException::withMessages(['category' => 'Kategori masih digunakan oleh produk. Nonaktifkan kategori bila tidak lagi dipakai.']);
        }
        $category->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}
