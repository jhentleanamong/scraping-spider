<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScraperRequest extends FormRequest
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
        return [
            'api_key' => ['required', 'string'],
            'url' => ['required', 'string', 'url'],
            'extract_rules' => ['nullable', 'string'],
            'screenshot' => ['nullable', 'boolean'],
            'async' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'screenshot' => $this->toBool($this->screenshot),
            'async' => $this->toBool($this->async),
        ]);
    }

    /**
     * Converts a value to a boolean.
     *
     * @param mixed $value The value to convert.
     * @return bool|null Boolean value or null on failure.
     */
    private function toBool(mixed $value): ?bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );
    }
}
