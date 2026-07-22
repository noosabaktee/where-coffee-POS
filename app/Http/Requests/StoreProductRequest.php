<?php

namespace App\Http\Requests;

use App\Models\Outlet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Outlet $outlet */
        $outlet = $this->attributes->get('current_outlet');

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:40', Rule::unique('products')->where('outlet_id', $outlet->id)],
            'barcode' => ['required', 'string', 'max:80', Rule::unique('products')->where('outlet_id', $outlet->id)],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'gte:cost_price'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'unit' => ['sometimes', 'string', 'max:20'],
            'image_data' => ['nullable', 'string', 'max:4200000'],
            'image_url' => ['nullable', 'url', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
