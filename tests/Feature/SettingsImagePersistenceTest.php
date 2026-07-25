<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\OutletSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsImagePersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_logo_keeps_existing_uploaded_qris(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $outlet = Outlet::query()->where('code', 'UTAMA')->firstOrFail();
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $setting = OutletSetting::query()->where('outlet_id', $outlet->id)->firstOrFail();
        Storage::disk('public')->put('branding/logos/old-logo.png', 'old-logo');
        Storage::disk('public')->put('branding/qris/old-qris.png', 'old-qris');
        $setting->update([
            'logo_path' => 'branding/logos/old-logo.png',
            'logo_url' => null,
            'qris_path' => 'branding/qris/old-qris.png',
            'qris_url' => null,
        ]);

        $this->actingAs($admin)
            ->withSession(['current_outlet_id' => $outlet->id])
            ->putJson('/api/settings', [
                'store_name' => $setting->store_name,
                'address' => $setting->address,
                'phone' => $setting->phone,
                'tax_rate' => $setting->tax_rate,
                'service_charge_rate' => $setting->service_charge_rate,
                'logo_data' => $this->tinyPngDataUri(),
                'qris_url' => asset('storage/branding/qris/old-qris.png'),
            ])
            ->assertOk();

        $setting->refresh();
        $this->assertNotSame('branding/logos/old-logo.png', $setting->logo_path);
        $this->assertSame('branding/qris/old-qris.png', $setting->qris_path);
        Storage::disk('public')->assertExists('branding/qris/old-qris.png');
    }

    public function test_updating_qris_keeps_existing_uploaded_logo(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $outlet = Outlet::query()->where('code', 'UTAMA')->firstOrFail();
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $setting = OutletSetting::query()->where('outlet_id', $outlet->id)->firstOrFail();
        Storage::disk('public')->put('branding/logos/old-logo.png', 'old-logo');
        Storage::disk('public')->put('branding/qris/old-qris.png', 'old-qris');
        $setting->update([
            'logo_path' => 'branding/logos/old-logo.png',
            'logo_url' => null,
            'qris_path' => 'branding/qris/old-qris.png',
            'qris_url' => null,
        ]);

        $this->actingAs($admin)
            ->withSession(['current_outlet_id' => $outlet->id])
            ->putJson('/api/settings', [
                'store_name' => $setting->store_name,
                'address' => $setting->address,
                'phone' => $setting->phone,
                'tax_rate' => $setting->tax_rate,
                'service_charge_rate' => $setting->service_charge_rate,
                'logo_url' => asset('storage/branding/logos/old-logo.png'),
                'qris_data' => $this->tinyPngDataUri(),
            ])
            ->assertOk();

        $setting->refresh();
        $this->assertSame('branding/logos/old-logo.png', $setting->logo_path);
        $this->assertNotSame('branding/qris/old-qris.png', $setting->qris_path);
        Storage::disk('public')->assertExists('branding/logos/old-logo.png');
    }

    private function tinyPngDataUri(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=';
    }
}
