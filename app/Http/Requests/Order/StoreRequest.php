<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $notAuth = is_null($this->user());

        return [
            'name' => [
                Rule::requiredIf($notAuth),
                'string',
            ],
            'phone' => [
                Rule::requiredIf($notAuth),
                'numeric',
            ],
            'email' => 'nullable|string|email',
            'address' => 'nullable|string',
            'comment' => 'nullable|string',
            'products' => 'required|array',
            'products.*' => 'array:product_id,count',
            'products.*.product_id' => 'required|numeric',
            'products.*.count' => 'required|numeric',
        ];
    }
}
