<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

abstract class ListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1', 'prohibits:cursor'],
            'cursor' => array_merge(['sometimes', 'prohibits:page'], $this->cursorSpec()['rules']),
            'size' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    protected abstract function cursorSpec(): array;

    public function messages(): array
    {
        $messages = [
            'page.integer' => 'Page must be an integer.',
            'page.min' => 'Page must be at least 1.',
            'page.prohibits' => 'Cannot use both page and cursor for pagination.',
            'cursor.prohibits' => 'Cannot use both page and cursor for pagination.',
            'size.integer' => 'Size must be an integer.',
            'size.min' => 'Size must be at least 1.',
            'size.max' => 'Size cannot be greater than 100.',
        ];
        return array_merge($messages, $this->cursorSpec()['messages']);
    }
}