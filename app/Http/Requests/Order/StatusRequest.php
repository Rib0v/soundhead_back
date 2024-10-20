<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StatusRequest extends FormRequest
{
    public int $status;

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
            'status' => 'required|numeric|exists:statuses,id'
        ];
    }

    public function messages()
    {
        return [
            'status.exists' => 'Указан id несуществующего статуса.',
        ];
    }

    protected function withValidator($validator)
    {
        $this->status = $validator->validated()['status'] ?? 0;
    }
}
