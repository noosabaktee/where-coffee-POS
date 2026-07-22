<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 'product_id', 'product_name', 'sku', 'barcode',
        'category_name', 'unit_cost', 'unit_price', 'quantity',
        'line_subtotal', 'line_cost', 'line_profit',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'line_subtotal' => 'decimal:2',
            'line_cost' => 'decimal:2',
            'line_profit' => 'decimal:2',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
