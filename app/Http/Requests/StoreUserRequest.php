<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'alpha_dash', 'max:60', Rule::unique('users', 'username')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'role' => ['required', Rule::in(['Administrator', 'Outlet', 'Kasir'])],
            'outlet_id' => ['nullable', 'integer', 'exists:outlets,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            if ($this->input('role') !== 'Administrator' && ! $this->filled('outlet_id')) {
                $validator->errors()->add('outlet_id', 'Outlet wajib dipilih untuk akun non-administrator.');
            }
        }];
    }
}
