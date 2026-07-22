<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:160'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'service_charge_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'logo_data' => ['nullable', 'string', 'max:4200000'],
            'logo_url' => ['nullable', 'url', 'max:2000'],
            'qris_data' => ['nullable', 'string', 'max:4200000'],
            'qris_url' => ['nullable', 'url', 'max:2000'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
            'points_per_amount' => ['sometimes', 'integer', 'min:1'],
            'point_value' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
