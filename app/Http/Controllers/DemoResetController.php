<?php

namespace App\Http\Controllers;

use Database\Seeders\DomainDemoSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DemoResetController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('demo.reset'), 403);
        abort_unless(config('wherecoffee.demo_reset_enabled'), 403, 'Reset demo dinonaktifkan pada environment ini.');

        foreach (['loyalty_transactions', 'stock_movements', 'transaction_items', 'transactions', 'expenses', 'products', 'customers', 'categories'] as $table) {
            DB::table($table)->delete();
        }

        Artisan::call('db:seed', ['--class' => DomainDemoSeeder::class, '--force' => true]);

        return response()->json(['message' => 'Data operasional demo berhasil dikembalikan ke kondisi awal.']);
    }
}
