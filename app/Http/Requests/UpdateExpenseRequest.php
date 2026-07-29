<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $expense = $this->route('expense');

        return [
            'expense_number' => ['sometimes', 'required', 'string', 'max:40', Rule::unique('expenses', 'expense_number')->ignore($expense)],
            'expense_date' => ['sometimes', 'date'],
            'category' => ['sometimes', 'required', 'string', 'max:80'],
            'description' => ['sometimes', 'required', 'string', 'max:255'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:1'],
            'payment_method' => ['sometimes', Rule::in(['Tunai', 'Transfer', 'Debit', 'QRIS'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
