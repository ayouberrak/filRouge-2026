<?php

namespace App\Modules\Marketplace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer|min:0',
            'quantity' => 'required|integer|min:0',
            'image' => 'nullable|string',
        ];
    }
}
