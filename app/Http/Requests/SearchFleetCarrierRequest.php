<?php

namespace App\Http\Requests;

class SearchFleetCarrierRequest extends BaseRequest
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
            'identifier' => 'sometimes|string|max:7',
            'withCommanderInformation' => 'sometimes|int|max:1',
            'withScheduleInformation' => 'sometimes|int|max:1',
            'exactSearch' => 'sometimes|int|max:1',
            'limit' => 'sometimes|int|max:100',
        ];
    }
}
