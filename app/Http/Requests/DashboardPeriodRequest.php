<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DashboardPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('dashboard.view') || $this->user()?->can('analytics.view');
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'required_with:to', 'date_format:Y-m-d', 'before_or_equal:today'],
            'to' => ['nullable', 'required_with:from', 'date_format:Y-m-d', 'after_or_equal:from', 'before_or_equal:today'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('from') || $validator->errors()->has('to') || ! $this->filled('from') || ! $this->filled('to')) {
                    return;
                }

                $days = CarbonImmutable::createFromFormat('Y-m-d', (string) $this->string('from'))
                    ->diffInDays(CarbonImmutable::createFromFormat('Y-m-d', (string) $this->string('to')));

                if ($days > 365) {
                    $validator->errors()->add('to', 'Rentang analisis maksimal 366 hari.');
                }
            },
        ];
    }
}
