<?php

namespace App\Http\Requests;

class StoreFleetScheduleRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'fleet_carrier_id' => 'required|exists:fleet_carriers,id',
            'departure' => 'required|string|max:60',
            'destination' => 'required|string|max:60',
            'title' => 'required|string|max:255',
            'description' => 'sometimes|string',
            'departs_at' => 'required|date',
        ];
    }
}
