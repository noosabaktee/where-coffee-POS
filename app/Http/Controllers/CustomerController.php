<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);
        $customers = Customer::query()->orderBy('name')->get();
        return response()->json(CustomerResource::collection($customers)->resolve($request));
    }

    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $needle = '%'.mb_strtolower($query).'%';
        $customers = Customer::query()
            ->active()
            ->where(function ($builder) use ($needle): void {
                $builder
                    ->whereRaw('LOWER(name) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(member_code) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(phone) LIKE ?', [$needle]);
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json(CustomerResource::collection($customers)->resolve($request));
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $this->authorize('create', Customer::class);
        $data = $request->validated();
        $data['member_code'] ??= 'MBR-'.now()->format('ym').'-'.Str::upper(Str::random(6));
        $customer = Customer::query()->create($data);

        return response()->json([
            'message' => 'Member berhasil ditambahkan.',
            'data' => (new CustomerResource($customer))->resolve($request),
        ], 201);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $this->authorize('update', $customer);
        $customer->update($request->validated());

        return response()->json([
            'message' => 'Data member berhasil diperbarui.',
            'data' => (new CustomerResource($customer->fresh()))->resolve($request),
        ]);
    }

    public function destroy(Request $request, Customer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);
        if ($customer->transactions()->exists()) {
            $customer->update(['is_active' => false]);
            return response()->json(['message' => 'Member memiliki riwayat transaksi dan telah dinonaktifkan.']);
        }
        $customer->delete();
        return response()->json(['message' => 'Member berhasil dihapus.']);
    }
}
