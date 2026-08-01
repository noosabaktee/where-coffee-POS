<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $type = $category->type;

        return [
            'type' => ['sometimes', Rule::in([Category::TYPE_PRODUCT, Category::TYPE_EXPENSE])],
            'code' => ['sometimes', 'string', 'max:30', Rule::unique('categories', 'code')->where('type', $type)->ignore($category)],
            'name' => ['sometimes', 'required', 'string', $type === Category::TYPE_EXPENSE ? 'max:80' : 'max:100', Rule::unique('categories', 'name')->where('type', $type)->ignore($category)],
            'icon' => ['nullable', 'string', 'max:60'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            $category = $this->route('category');

            if ($this->has('type') && $this->input('type') !== $category->type) {
                $validator->errors()->add('type', 'Jenis kategori tidak dapat diubah setelah kategori dibuat.');
            }
        }];
    }
}
