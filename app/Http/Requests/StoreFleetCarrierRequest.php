<?php

namespace App\Http\Requests;

class StoreFleetCarrierRequest extends BaseRequest
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
            'identifier' => 'required|string|unique:fleet_carriers|max:7'
        ];
    }
}
