<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_code' => $this->member_code,
            'name' => $this->name,
            'phone' => $this->when($request->user()?->can('customers.update'), $this->phone),
            'email' => $this->when($request->user()?->can('customers.update'), $this->email),
            'tier' => $this->tier,
            'points' => $this->points,
            'birth_date' => $this->when($request->user()?->can('customers.update'), $this->birth_date?->toDateString()),
            'notes' => $this->when($request->user()?->can('customers.update'), $this->notes),
            'last_visit_at' => $this->when($request->user()?->can('customers.update'), $this->last_visit_at?->toIso8601String()),
            'is_active' => $this->is_active,
        ];
    }
}
