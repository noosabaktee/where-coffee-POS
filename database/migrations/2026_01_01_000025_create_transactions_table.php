<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->timestamp('transacted_at')->index();
            $table->string('status', 20)->default('paid')->index();
            $table->string('payment_method', 30)->index();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('service_charge_percentage', 5, 2)->default(0);
            $table->decimal('service_charge_amount', 15, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->unsignedInteger('points_redeemed')->default(0);
            $table->decimal('points_discount_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);
            $table->decimal('amount_paid', 15, 2);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->decimal('cost_total', 15, 2)->default(0);
            $table->decimal('gross_profit', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['outlet_id', 'transacted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
