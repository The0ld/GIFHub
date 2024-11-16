<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GifFilterRequest extends FormRequest
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
            'q' => 'required|string|max:255',
            'limit' => 'nullable|integer|min:1',
            'offset' => 'nullable|integer|min:0'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'limit' => $this->input('limit', 10),
            'offset' => $this->input('offset', 0),
        ]);
    }
}
