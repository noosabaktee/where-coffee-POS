<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id', 'created_by', 'expense_number', 'expense_date', 'category',
        'description', 'amount', 'payment_method', 'receipt_path', 'notes',
    ];

    protected function casts(): array
    {
        return ['expense_date' => 'date', 'amount' => 'decimal:2'];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForOutlet(Builder $query, Outlet|int $outlet): Builder
    {
        return $query->where('outlet_id', $outlet instanceof Outlet ? $outlet->getKey() : $outlet);
    }
}
