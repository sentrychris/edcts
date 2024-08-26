<?php

namespace App\Http\Requests;

class SearchSystemByDistanceRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'x' => 'required|int',
            'y' => 'required|int',
            'z' => 'required|int',
            'limit' => 'sometimes|int|max:1000',
        ];
    }
}
