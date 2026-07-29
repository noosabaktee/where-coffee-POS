<?php

namespace Tests\Feature;

use App\Models\Category;
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
            ->assertSee('Dibuat otomatis saat menambah menu dan tetap bisa diubah.')
            ->assertSee('inventoryStatusFilter', false)
            ->assertSee('<option value="active" selected>Produk Aktif</option>', false)
            ->assertSee('Status Produk');
    }

    public function test_category_modal_contains_code_input_and_save_wiring(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get('/kategori')
            ->assertOk()
            ->assertSee('id="catCode"', false)
            ->assertSee('Contoh: CAT-PST')
            ->assertSee('Cari ID CODE atau nama kategori...')
            ->assertSee('ID CODE');

        $script = file_get_contents(public_path('js/where-coffee.js'));

        $this->assertStringContainsString("byId('catCode').value = category.code || ''", $script);
        $this->assertStringContainsString("code: byId('catCode').value.trim().toUpperCase()", $script);
    }

    public function test_expense_modal_contains_editable_auto_generated_code(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get('/biaya-operasional')
            ->assertOk()
            ->assertSee('id="expCode"', false)
            ->assertSee('Dibuat otomatis saat menambah biaya dan tetap bisa diubah.')
            ->assertSee('Cari ID CODE atau rincian pengeluaran...')
            ->assertSee('ID CODE');

        $script = file_get_contents(public_path('js/where-coffee.js'));

        $this->assertStringContainsString("byId('expCode').value = suggestedReferences.expense_number || ''", $script);
        $this->assertStringContainsString("byId('expCode').value = expense.expense_number", $script);
        $this->assertStringContainsString("expense_number: byId('expCode').value.trim().toUpperCase()", $script);
    }

    public function test_inventory_and_expense_receive_server_generated_reference_suggestions(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $inventory = $this->actingAs($admin)
            ->getJson('/api/bootstrap?page=inventori')
            ->assertOk()
            ->json('suggested_references.product_sku');

        $expense = $this->actingAs($admin)
            ->getJson('/api/bootstrap?page=biaya')
            ->assertOk()
            ->json('suggested_references.expense_number');

        $this->assertSame('UTAMA-018', $inventory);
        $this->assertMatchesRegularExpression('/^EXP-UTAMA-\d{6}-[A-Z0-9]{4}$/', $expense);
    }

    public function test_expense_code_can_be_added_and_edited(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $created = $this->actingAs($admin)
            ->postJson('/api/expenses', [
                'expense_number' => 'EXP-TEST-001',
                'category' => 'Lain-lain',
                'description' => 'Biaya pengujian',
                'amount' => 10000,
                'payment_method' => 'Tunai',
            ])
            ->assertCreated()
            ->assertJsonPath('data.expense_number', 'EXP-TEST-001')
            ->json('data.id');

        $this->actingAs($admin)
            ->putJson("/api/expenses/{$created}", [
                'expense_number' => 'EXP-TEST-002',
            ])
            ->assertOk()
            ->assertJsonPath('data.expense_number', 'EXP-TEST-002');
    }

    public function test_duplicate_validation_message_is_in_indonesian(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $category = Category::query()->firstOrFail();

        $this->actingAs($admin)
            ->postJson('/api/categories', [
                'code' => $category->code,
                'name' => 'Kategori Uji Duplikat',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.code.0', 'ID CODE sudah digunakan.');
    }
}
