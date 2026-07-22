<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('outlet_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('outlet_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('store_name', 160);
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(10);
            $table->decimal('service_charge_rate', 5, 2)->default(0);
            $table->string('logo_path')->nullable();
            $table->text('logo_url')->nullable();
            $table->string('qris_path')->nullable();
            $table->text('qris_url')->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->string('timezone', 60)->default('Asia/Jakarta');
            $table->string('receipt_footer', 255)->default('Terima kasih atas kunjungan Anda');
            $table->unsignedSmallInteger('points_per_amount')->default(10000);
            $table->unsignedSmallInteger('point_value')->default(500);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlet_settings');
    }
};
