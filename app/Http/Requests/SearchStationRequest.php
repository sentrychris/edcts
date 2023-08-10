<?php

namespace App\Http\Requests;

class SearchStationRequest extends APIRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string',
            'withSystem' => 'sometimes|integer|max:1',
            'exactSearch' => 'sometimes|integer|max:1',
            'limit' => 'sometimes|int|max:100',
        ];
    }
}
