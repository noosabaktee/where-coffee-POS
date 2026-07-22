<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'username' => ['sometimes', 'required', 'alpha_dash', 'max:60', Rule::unique('users', 'username')->ignore($user)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'role' => ['sometimes', 'required', Rule::in(['Administrator', 'Outlet', 'Kasir'])],
            'outlet_id' => ['nullable', 'integer', 'exists:outlets,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            $role = $this->input('role', $this->route('user')->getRoleNames()->first());
            $outletId = $this->input('outlet_id', $this->route('user')->outlet_id);
            if ($role !== 'Administrator' && ! $outletId) {
                $validator->errors()->add('outlet_id', 'Outlet wajib dipilih untuk akun non-administrator.');
            }
        }];
    }
}
