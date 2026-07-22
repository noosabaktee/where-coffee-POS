<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOutletRequest;
use App\Http\Requests\UpdateOutletRequest;
use App\Http\Resources\OutletResource;
use App\Models\Outlet;
use App\Models\OutletSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OutletController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Outlet::class);

        $outlets = Outlet::query()
            ->withCount(['users', 'products', 'transactions'])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return response()->json(OutletResource::collection($outlets)->resolve($request));
    }

    public function store(StoreOutletRequest $request): JsonResponse
    {
        $this->authorize('create', Outlet::class);

        $outlet = DB::transaction(function () use ($request): Outlet {
            $outlet = Outlet::query()->create($request->validated());
            OutletSetting::query()->create([
                'outlet_id' => $outlet->id,
                'store_name' => $outlet->name,
                'address' => $outlet->address,
                'phone' => $outlet->phone,
                'currency' => 'IDR',
                'timezone' => $outlet->timezone,
                'tax_rate' => 10,
                'service_charge_rate' => 0,
                'receipt_footer' => 'Terima kasih sudah ngopi bersama Where Coffee ☕',
                'points_per_amount' => 10000,
                'point_value' => 500,
            ]);

            return $outlet;
        });

        return response()->json([
            'message' => 'Cabang berhasil ditambahkan.',
            'data' => (new OutletResource($outlet))->resolve($request),
        ], 201);
    }

    public function update(UpdateOutletRequest $request, Outlet $outlet): JsonResponse
    {
        $this->authorize('update', $outlet);

        DB::transaction(function () use ($request, $outlet): void {
            $outlet->update($request->validated());
            OutletSetting::query()->updateOrCreate(
                ['outlet_id' => $outlet->id],
                [
                    'store_name' => $outlet->name,
                    'address' => $outlet->address,
                    'phone' => $outlet->phone,
                    'timezone' => $outlet->timezone,
                ],
            );
        });

        return response()->json([
            'message' => 'Cabang berhasil diperbarui.',
            'data' => (new OutletResource($outlet->fresh()))->resolve($request),
        ]);
    }

    public function destroy(Request $request, Outlet $outlet): JsonResponse
    {
        $this->authorize('delete', $outlet);

        if (Outlet::query()->where('is_active', true)->count() <= 1 && $outlet->is_active) {
            throw ValidationException::withMessages(['outlet' => 'Minimal harus ada satu cabang aktif.']);
        }

        if ((int) $request->session()->get('current_outlet_id') === (int) $outlet->id) {
            $request->session()->forget('current_outlet_id');
        }

        $hasDependencies = $outlet->users()->exists()
            || $outlet->products()->exists()
            || $outlet->transactions()->exists()
            || $outlet->expenses()->exists();

        if ($hasDependencies) {
            $outlet->update(['is_active' => false]);
            return response()->json(['message' => 'Cabang memiliki histori transaksi/data dan telah dinonaktifkan.']);
        }

        DB::transaction(function () use ($outlet): void {
            $outlet->setting()?->delete();
            $outlet->delete();
        });

        return response()->json(['message' => 'Cabang berhasil dihapus.']);
    }
}
