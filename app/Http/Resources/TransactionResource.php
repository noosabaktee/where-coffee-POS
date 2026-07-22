<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'time' => $this->transacted_at?->timezone($this->outlet?->timezone ?? config('app.timezone'))->format('d/m/Y H:i'),
            'transacted_at' => $this->transacted_at?->toIso8601String(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->product_id,
                'product_id' => $item->product_id,
                'name' => $item->product_name,
                'sku' => $item->sku,
                'barcode' => $item->barcode,
                'category' => $item->category_name,
                'capital' => $this->when($request->user()?->can('reports.view'), (float) $item->unit_cost),
                'price' => (float) $item->unit_price,
                'qty' => $item->quantity,
                'subtotal' => (float) $item->line_subtotal,
                'profit' => $this->when($request->user()?->can('reports.view'), (float) $item->line_profit),
            ])),
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount_percentage,
            'discount_amount' => (float) $this->discount_amount,
            'service_charge_percentage' => (float) $this->service_charge_percentage,
            'service_charge_amount' => (float) $this->service_charge_amount,
            'tax_percentage' => (float) $this->tax_percentage,
            'tax_amount' => (float) $this->tax_amount,
            'points_redeemed' => $this->points_redeemed,
            'points_discount_amount' => (float) $this->points_discount_amount,
            'total' => (float) $this->grand_total,
            'pay' => (float) $this->amount_paid,
            'change' => (float) $this->change_amount,
            'profit' => $this->when($request->user()?->can('reports.view'), (float) $this->gross_profit),
            'payMode' => $this->payment_method,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'outlet_id' => $this->outlet_id,
            'outlet' => $this->outlet?->name,
            'cashier' => $this->user?->name,
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'member_code' => $this->customer->member_code,
            ] : null,
        ];
    }
}
