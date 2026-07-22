<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $outlet = $this->route('outlet');

        return [
            'code' => ['sometimes', 'required', 'string', 'max:40', 'alpha_dash', Rule::unique('outlets', 'code')->ignore($outlet)],
            'name' => ['sometimes', 'required', 'string', 'max:160'],
            'address' => ['sometimes', 'required', 'string', 'max:500'],
            'phone' => ['sometimes', 'required', 'string', 'max:50'],
            'timezone' => ['sometimes', 'required', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
