<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class SearchSystemRouteRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'from' => 'required|string|exists:systems,slug',
            'to' => 'required|string|exists:systems,slug',
            'ly' => 'required|numeric|min:1|max:500',
        ];
    }
}
