<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPeriodAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_metrics_can_be_filtered_by_date_range(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $from = now()->subDays(29)->toDateString();
        $to = now()->toDateString();

        $this->actingAs($admin)
            ->getJson("/api/dashboard?from={$from}&to={$to}")
            ->assertOk()
            ->assertJsonPath('period.from', $from)
            ->assertJsonPath('period.to', $to)
            ->assertJsonStructure([
                'summary' => [
                    'revenue', 'gross_profit', 'expenses', 'net_profit',
                    'transaction_count', 'average_basket', 'items_sold',
                    'gross_margin', 'net_margin', 'member_rate',
                ],
                'comparison' => ['revenue', 'net_profit', 'transaction_count'],
                'trend',
                'top_products',
                'category_contribution',
                'payment_mix',
                'peak_hours',
                'insights',
            ]);
    }

    public function test_dashboard_rejects_period_longer_than_one_year(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->getJson('/api/dashboard?from=2024-01-01&to=2026-01-01')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('to');
    }

    public function test_dashboard_and_analytics_pages_contain_period_controls_and_extended_metrics(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('periodFrom', false)
            ->assertSee('periodTo', false)
            ->assertSee('monthProjection', false)
            ->assertSee('paymentMixChart', false);

        $this->actingAs($admin)->get('/analisis-bisnis')
            ->assertOk()
            ->assertSee('topProductsChart', false)
            ->assertSee('peakHoursChart', false)
            ->assertSee('anRepeatRate', false);
    }
}
