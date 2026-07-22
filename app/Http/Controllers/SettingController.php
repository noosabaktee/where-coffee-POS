<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Http\Resources\SettingResource;
use App\Models\Outlet;
use App\Models\OutletSetting;
use App\Services\ImageStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var Outlet $outlet */
        $outlet = $request->attributes->get('current_outlet');
        $setting = $this->setting($outlet);
        $this->authorize('view', $setting);
        return response()->json((new SettingResource($setting))->resolve($request));
    }

    public function update(UpdateSettingsRequest $request, ImageStorageService $images): JsonResponse
    {
        /** @var Outlet $outlet */
        $outlet = $request->attributes->get('current_outlet');
        $setting = $this->setting($outlet);
        $this->authorize('update', $setting);

        $data = $request->safe()->except(['logo_data', 'qris_data']);
        if ($request->filled('logo_data')) {
            $data['logo_path'] = $images->storeDataUri($request->input('logo_data'), 'branding/logos', $setting->logo_path);
            $data['logo_url'] = null;
        } elseif ($request->filled('logo_url')) {
            $images->delete($setting->logo_path);
            $data['logo_path'] = null;
        }
        if ($request->filled('qris_data')) {
            $data['qris_path'] = $images->storeDataUri($request->input('qris_data'), 'branding/qris', $setting->qris_path);
            $data['qris_url'] = null;
        } elseif ($request->filled('qris_url')) {
            $images->delete($setting->qris_path);
            $data['qris_path'] = null;
        }

        $setting->update($data);
        $outlet->update([
            'name' => $setting->store_name,
            'address' => $setting->address,
            'phone' => $setting->phone,
        ]);

        return response()->json([
            'message' => 'Pengaturan outlet berhasil disimpan.',
            'data' => (new SettingResource($setting->fresh()))->resolve($request),
        ]);
    }

    private function setting(Outlet $outlet): OutletSetting
    {
        return OutletSetting::query()->firstOrCreate(
            ['outlet_id' => $outlet->id],
            ['store_name' => $outlet->name, 'address' => $outlet->address, 'phone' => $outlet->phone, 'timezone' => $outlet->timezone],
        );
    }
}
