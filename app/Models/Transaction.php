<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id', 'user_id', 'customer_id', 'invoice_number', 'transacted_at',
        'status', 'payment_method', 'subtotal', 'discount_percentage', 'discount_amount',
        'service_charge_percentage', 'service_charge_amount', 'tax_percentage', 'tax_amount',
        'points_redeemed', 'points_discount_amount', 'grand_total', 'amount_paid',
        'change_amount', 'cost_total', 'gross_profit', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'transacted_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'service_charge_percentage' => 'decimal:2',
            'service_charge_amount' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'points_redeemed' => 'integer',
            'points_discount_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'cost_total' => 'decimal:2',
            'gross_profit' => 'decimal:2',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function scopeForOutlet(Builder $query, Outlet|int $outlet): Builder
    {
        return $query->where('outlet_id', $outlet instanceof Outlet ? $outlet->getKey() : $outlet);
    }
}
