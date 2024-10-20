<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public string $newPassword;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        /**
         * Получаем полную модель юзера из БД,
         * чтобы сработала проверка current_password
         */
        $this->user()?->get();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'old_password' => 'required|min:3|current_password',
            'new_password' => 'required|min:3|confirmed',
        ];
    }

    protected function withValidator($validator)
    {
        $this->newPassword = $validator->validated()['new_password'] ?? '';
    }
}
