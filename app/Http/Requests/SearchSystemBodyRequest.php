<?php

namespace App\Http\Requests;

class SearchSystemBodyRequest extends APIRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'system' => 'sometimes|string',
            'name' => 'sometimes|string',
            'type' => 'sometimes|string',
            'withSystem' => 'sometimes|int|max:1',
            'withStations' => 'sometimes|int|max:1',
        ];
    }
}
