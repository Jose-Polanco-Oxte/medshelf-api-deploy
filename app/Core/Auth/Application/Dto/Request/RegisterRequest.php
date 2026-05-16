<?php

namespace App\Core\Auth\Application\Dto\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->whereNull('deleted_at')],
            'password' => ['required', 'string', 'min:5'],
        ];
    }
}
