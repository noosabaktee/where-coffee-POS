<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_number' => ['nullable', 'string', 'max:40', Rule::unique('expenses', 'expense_number')],
            'expense_date' => ['nullable', 'date'],
            'category' => [
                'required',
                'string',
                'max:80',
                Rule::exists('categories', 'name')
                    ->where('type', Category::TYPE_EXPENSE)
                    ->where('is_active', true),
            ],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['sometimes', Rule::in(['Tunai', 'Transfer', 'Debit', 'QRIS'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
