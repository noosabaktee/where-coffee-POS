<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseCategoryMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_category_page_has_separate_product_and_expense_tabs(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get('/kategori')
            ->assertOk()
            ->assertSee('categoryProductTab', false)
            ->assertSee('categoryExpenseTab', false)
            ->assertSee('Kategori Produk')
            ->assertSee('Kategori Biaya Operasional');
    }

    public function test_expense_page_receives_only_expense_categories_from_master(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $categories = $this->actingAs($admin)
            ->getJson('/api/bootstrap?page=biaya')
            ->assertOk()
            ->json('categories');

        $this->assertNotEmpty($categories);
        $this->assertSame([Category::TYPE_EXPENSE], collect($categories)->pluck('type')->unique()->values()->all());
    }

    public function test_expense_category_can_be_added_used_and_renamed_from_master(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $categoryId = $this->actingAs($admin)
            ->postJson('/api/categories', [
                'type' => Category::TYPE_EXPENSE,
                'code' => 'EXP-SEWA',
                'name' => 'Sewa Tempat',
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', Category::TYPE_EXPENSE)
            ->json('data.id');

        $expenseId = $this->actingAs($admin)
            ->postJson('/api/expenses', [
                'expense_number' => 'EXP-MASTER-001',
                'category' => 'Sewa Tempat',
                'description' => 'Sewa outlet bulanan',
                'amount' => 5000000,
                'payment_method' => 'Transfer',
            ])
            ->assertCreated()
            ->json('data.id');

        $this->actingAs($admin)
            ->putJson("/api/categories/{$categoryId}", [
                'type' => Category::TYPE_EXPENSE,
                'name' => 'Sewa Outlet',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Sewa Outlet');

        $this->assertSame('Sewa Outlet', Expense::query()->findOrFail($expenseId)->category);

        $this->actingAs($admin)
            ->deleteJson("/api/categories/{$categoryId}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category');
    }

    public function test_product_cannot_use_an_expense_category(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $category = Category::query()->ofType(Category::TYPE_EXPENSE)->firstOrFail();

        $this->actingAs($admin)
            ->postJson('/api/products', [
                'category_id' => $category->id,
                'barcode' => 'INVALID-EXP-CATEGORY',
                'name' => 'Produk Salah Kategori',
                'cost_price' => 10000,
                'selling_price' => 15000,
                'stock' => 10,
                'min_stock' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category_id');
    }
}
