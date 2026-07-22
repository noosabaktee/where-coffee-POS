<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'expense_number' => $this->expense_number,
            'date' => $this->expense_date?->format('d/m/Y'),
            'expense_date' => $this->expense_date?->toDateString(),
            'category' => $this->category,
            'desc' => $this->description,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'outlet_id' => $this->outlet_id,
            'outlet' => $this->outlet?->name,
            'created_by' => $this->creator?->name,
        ];
    }
}
