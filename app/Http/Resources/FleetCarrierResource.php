<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FleetCarrierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'identifier' => $this->identifier,
            'services' => [
                'repair' => !!$this->has_repair,
                'refuel' => !!$this->has_refuel,
                'armory' => !!$this->has_armory,
                'shipyard' => !!$this->has_shipyard,
                'outfitting' => !!$this->has_outfitting,
                'cartographics' => !!$this->has_cartographics,
            ],
            'schedule' => FleetScheduleResource::collection($this->whenLoaded('schedule'))
        ];
    }
}
