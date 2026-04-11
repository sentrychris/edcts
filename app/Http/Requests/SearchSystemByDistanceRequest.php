<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class SearchSystemByDistanceRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'slug' => ['sometimes', 'string', 'exists:systems,slug'],
            'x' => ['required_without:slug', 'numeric'],
            'y' => ['required_without:slug', 'numeric'],
            'z' => ['required_without:slug', 'numeric'],
            'ly' => ['sometimes', 'int', 'max:5000'],
            'limit' => ['sometimes', 'int', 'max:1000'],
        ];
    }
}
