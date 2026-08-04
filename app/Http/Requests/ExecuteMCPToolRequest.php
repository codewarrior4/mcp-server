<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExecuteMCPToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tool_name' => ['required', 'string'],
            'parameters' => ['required', 'array'],
            'request_id' => ['nullable', 'string', 'max:255'],
            'metadata' => ['sometimes', 'array'],
        ];
    }
}
