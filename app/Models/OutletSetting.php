<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OutletSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id', 'store_name', 'address', 'phone', 'tax_rate',
        'service_charge_rate', 'logo_path', 'logo_url', 'qris_path',
        'qris_url', 'currency', 'timezone', 'receipt_footer',
        'points_per_amount', 'point_value',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'service_charge_rate' => 'decimal:2',
            'points_per_amount' => 'integer',
            'point_value' => 'integer',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function getLogoAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : $this->logo_url;
    }

    public function getQrisImageAttribute(): ?string
    {
        return $this->qris_path ? Storage::disk('public')->url($this->qris_path) : $this->qris_url;
    }
}
