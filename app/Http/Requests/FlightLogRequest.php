<?php

namespace App\Http\Requests;

class FlightLogRequest extends APIRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'startDateTime' => 'required|date_format:Y-m-d H:i:s',
            'endDateTime' => 'required|date_format:Y-m-d H:i:s'
        ];
    }
}
