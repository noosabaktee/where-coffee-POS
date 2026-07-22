<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            'member_code' => ['sometimes', 'required', 'string', 'max:40', Rule::unique('customers', 'member_code')->ignore($customer)],
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'phone' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('customers', 'phone')->ignore($customer)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer)],
            'tier' => ['sometimes', Rule::in(['Bronze', 'Silver', 'Gold'])],
            'points' => ['sometimes', 'integer', 'min:0'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
