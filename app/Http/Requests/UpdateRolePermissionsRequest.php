<?php

namespace App\Http\Requests;

use App\Services\MenuPermissionMap;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'menus' => ['required', 'array'],
            'menus.*' => ['string', Rule::in(array_keys(MenuPermissionMap::all()))],
        ];
    }
}
