<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendRevisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_contains_checkout_confirmation_static_qris_and_tax_breakdown(): void
    {
        $this->seed(DatabaseSeeder::class);
        $cashier = User::query()->where('username', 'kasir1')->firstOrFail();

        $this->actingAs($cashier)
            ->get('/pos')
            ->assertOk()
            ->assertSee('confirmCheckout()', false)
            ->assertSee('cartTaxAmount', false)
            ->assertSee('images/qris/where-coffee-qris.png', false)
            ->assertSee('data-number-format', false);
    }

    public function test_table_pages_render_pagination_mount_points(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $pages = [
            '/inventori' => 'inventoryPagination',
            '/laporan' => 'reportPagination',
            '/biaya-operasional' => 'expensePagination',
            '/kategori' => 'categoryPagination',
            '/crm' => 'crmPagination',
            '/cabang' => 'outletPagination',
            '/pengaturan' => 'userPagination',
        ];

        foreach ($pages as $uri => $paginationId) {
            $this->actingAs($admin)
                ->get($uri)
                ->assertOk()
                ->assertSee($paginationId, false);
        }
    }

    public function test_application_uses_page_skeleton_and_separate_action_loading_modal(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('pageSkeleton', false)
            ->assertSee('pageContent', false)
            ->assertSee('actionLoadingModal', false)
            ->assertSee('Sedang meracik prosesmu', false)
            ->assertSee('action-steam', false)
            ->assertDontSee('globalLoadingOverlay', false);
    }

    public function test_inventory_contains_image_preview_and_active_status_filter(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get('/inventori')
            ->assertOk()
            ->assertSee('pImgPreview', false)
            ->assertSee('id="pSku"', false)
            ->assertSee('Contoh: UTAMA-012')
            ->assertSee('inventoryStatusFilter', false)
            ->assertSee('<option value="active" selected>Produk Aktif</option>', false)
            ->assertSee('Status Produk');
    }
}
