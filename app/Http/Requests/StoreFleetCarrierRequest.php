<?php

namespace App\Http\Requests;

class StoreFleetCarrierRequest extends APIRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:25',
            'identifier' => 'required|string|unique:fleet_carriers|max:7',
            'has_refuel' => 'sometimes|boolean',
            'has_repair' => 'sometimes|boolean',
            'has_armory' => 'sometimes|boolean',
            'has_shipyard' => 'sometimes|boolean',
            'has_outfitting' => 'sometimes|boolean',
            'has_cartographics' => 'sometimes|boolean',
        ];
    }
}
