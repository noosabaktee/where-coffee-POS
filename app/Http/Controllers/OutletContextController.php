<?php

namespace App\Http\Controllers;

use App\Http\Requests\SwitchOutletRequest;
use App\Http\Resources\OutletResource;
use App\Models\Outlet;
use Illuminate\Http\JsonResponse;

class OutletContextController extends Controller
{
    public function update(SwitchOutletRequest $request): JsonResponse
    {
        $outlet = Outlet::query()->where('is_active', true)->findOrFail($request->integer('outlet_id'));
        abort_unless($request->user()->canAccessOutlet($outlet), 403);

        $request->session()->put('current_outlet_id', $outlet->id);

        return response()->json([
            'message' => "Outlet aktif diubah ke {$outlet->name}.",
            'outlet' => (new OutletResource($outlet))->resolve($request),
        ]);
    }
}
