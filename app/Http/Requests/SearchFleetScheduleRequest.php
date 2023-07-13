<?php

namespace App\Http\Requests;

class SearchFleetScheduleRequest extends BaseRequest
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
            'limit' => 'sometimes|int|max:100'
        ];
    }
}
