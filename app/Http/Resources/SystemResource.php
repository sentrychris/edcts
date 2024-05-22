<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemResource extends JsonResource
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
            'id64'=> $this->id64,
            'name' => $this->name,
            'coords' => json_decode($this->coords),
            'information' => new SystemInformationResource($this->whenLoaded('information')),
            'bodies' => $this->whenLoaded('bodies'),
            'stations' =>  SystemStationResource::collection($this->whenLoaded('stations')), 
            'departures' => FleetScheduleResource::collection($this->whenLoaded('departures')),
            'arrivals' => FleetScheduleResource::collection($this->whenLoaded('arrivals')),
            'updated_at' => $this->updated_at,
            'slug' => $this->slug
        ];
    }
}
