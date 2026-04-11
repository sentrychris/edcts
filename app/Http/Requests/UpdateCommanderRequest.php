<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class UpdateCommanderRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'inara_api_key' => ['sometimes', 'nullable', 'string', 'max:255'],
            'edsm_api_key' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
