<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 160);
            $table->string('sku', 40);
            $table->string('barcode', 80)->nullable();
            $table->string('category_name', 100)->nullable();
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_subtotal', 15, 2);
            $table->decimal('line_cost', 15, 2);
            $table->decimal('line_profit', 15, 2);
            $table->timestamps();
            $table->index(['transaction_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
