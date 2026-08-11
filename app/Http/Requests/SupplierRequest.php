<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // apni auth policy ke hisaab se change kar lein
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'address'          => ['nullable', 'string', 'max:255'],
            'opening_balance'  => ['nullable', 'numeric', 'min:0'],
        ];
    }
}