<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_code' => ['nullable', 'string', 'max:40', Rule::unique('customers', 'member_code')],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30', Rule::unique('customers', 'phone')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')],
            'tier' => ['sometimes', Rule::in(['Bronze', 'Silver', 'Gold'])],
            'points' => ['sometimes', 'integer', 'min:0'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
