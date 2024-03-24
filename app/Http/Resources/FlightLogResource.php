<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlightLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'system' => new SystemResource($this->whenLoaded('systemInformation')),
            'discovered' => $this->first_discover,
            'visited_at' => $this->visited_at
        ];
    }
}
