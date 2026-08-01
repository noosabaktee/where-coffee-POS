<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Outlet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Outlet $outlet */
        $outlet = $this->attributes->get('current_outlet');
        $product = $this->route('product');

        return [
            'category_id' => ['sometimes', 'required', 'integer', Rule::exists('categories', 'id')->where('type', Category::TYPE_PRODUCT)],
            'sku' => ['sometimes', 'required', 'string', 'max:40', Rule::unique('products')->where('outlet_id', $outlet->id)->ignore($product)],
            'barcode' => ['sometimes', 'required', 'string', 'max:80', Rule::unique('products')->where('outlet_id', $outlet->id)->ignore($product)],
            'name' => ['sometimes', 'required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cost_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'selling_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'min_stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'unit' => ['sometimes', 'string', 'max:20'],
            'image_data' => ['nullable', 'string', 'max:4200000'],
            'image_url' => ['nullable', 'url', 'max:2000'],
            'remove_image' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            $cost = (float) ($this->input('cost_price', $this->route('product')->cost_price));
            $selling = (float) ($this->input('selling_price', $this->route('product')->selling_price));
            if ($selling < $cost) {
                $validator->errors()->add('selling_price', 'Harga jual tidak boleh lebih kecil dari harga modal.');
            }
        }];
    }
}
