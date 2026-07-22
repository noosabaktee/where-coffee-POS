<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'outlet_id' => $this->outlet_id,
            'category_id' => $this->category_id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category?->name,
            'capital' => $this->when($request->user()?->can('inventory.view') || $request->user()?->can('reports.view'), (float) $this->cost_price),
            'price' => (float) $this->selling_price,
            'stock' => $this->stock,
            'minStock' => $this->min_stock,
            'unit' => $this->unit,
            'image' => $this->image,
            'is_active' => $this->is_active,
            'is_low_stock' => $this->is_low_stock,
            'outlet' => $this->outlet?->name,
        ];
    }
}
