<?php

namespace App\Http\Requests;

class SearchFleetScheduleRequest extends APIRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'departure' => 'sometimes|string|max:60',
            'destination' => 'sometimes|string|max:60',
            'departs_at' => 'sometimes|date',
            'withCarrierInformation' => 'sometimes|int|max:1',
            'withSystemInformation' => 'sometimes|int|max:1',
            'exactSearch' => 'sometimes|int|max:1',
            'limit' => 'sometimes|int|max:100',
        ];
    }
}
