<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SwitchOutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('outlets.switch') ?? false;
    }

    public function rules(): array
    {
        return ['outlet_id' => ['required', 'integer', 'exists:outlets,id']];
    }
}
