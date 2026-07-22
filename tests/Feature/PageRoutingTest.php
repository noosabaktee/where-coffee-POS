<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_the_login_page(): void
    {
        $this->get('/dashboard')
            ->assertRedirect('/login');
    }

    public function test_each_application_page_uses_a_separate_blade_view(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $pages = [
            '/dashboard' => 'pages.dashboard',
            '/analisis-bisnis' => 'pages.analytics',
            '/pos' => 'pages.pos',
            '/inventori' => 'pages.inventory',
            '/laporan' => 'pages.reports',
            '/biaya-operasional' => 'pages.expenses',
            '/kategori' => 'pages.categories',
            '/crm' => 'pages.crm',
            '/pengaturan' => 'pages.settings',
            '/cabang' => 'pages.outlets',
        ];

        foreach ($pages as $uri => $view) {
            $this->actingAs($admin)
                ->get($uri)
                ->assertOk()
                ->assertViewIs($view);
        }
    }

    public function test_cashier_cannot_open_administrator_outlet_management_page(): void
    {
        $this->seed(DatabaseSeeder::class);
        $cashier = User::query()->where('username', 'kasir1')->firstOrFail();

        $this->actingAs($cashier)
            ->get('/cabang')
            ->assertForbidden();
    }
}
