<?php

namespace App\Http\Requests;

class SearchSystemRequest extends BaseRequest
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
            'withInformation' => 'sometimes|integer|max:1',
            'withBodies' => 'sometimes|integer|max:1',
            'withDepartures' => 'sometimes|integer|max:1',
            'withArrivals' => 'sometimes|integer|max:1',
            'exactSearch' => 'sometimes|integer|max:1',
            'limit' => 'sometimes|int|max:100',
        ];
    }
}
