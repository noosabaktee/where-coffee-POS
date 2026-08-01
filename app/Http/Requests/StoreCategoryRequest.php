<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('type')) {
            $this->merge(['type' => Category::TYPE_PRODUCT]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = (string) $this->input('type', Category::TYPE_PRODUCT);

        return [
            'type' => ['required', Rule::in([Category::TYPE_PRODUCT, Category::TYPE_EXPENSE])],
            'code' => ['nullable', 'string', 'max:30', Rule::unique('categories', 'code')->where('type', $type)],
            'name' => ['required', 'string', $type === Category::TYPE_EXPENSE ? 'max:80' : 'max:100', Rule::unique('categories', 'name')->where('type', $type)],
            'icon' => ['nullable', 'string', 'max:60'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
