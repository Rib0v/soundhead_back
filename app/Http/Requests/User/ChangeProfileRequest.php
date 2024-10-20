<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ChangeProfileRequest extends FormRequest
{
    public string $name;
    public string $phone;

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
        return [
            'name' => 'required|string',
            'phone' => 'required|string',
        ];
    }

    protected function withValidator($validator)
    {
        $validated = $validator->validated();

        $this->name = $validated['name'] ?? '';
        $this->phone = $validated['phone'] ?? '';
    }
}
