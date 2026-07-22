<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'discount_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'payment_method' => ['required', Rule::in(['Tunai', 'QRIS', 'Debit', 'Transfer'])],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'use_points' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
