<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FleetScheduleResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'departure' => new SystemResource($this->whenLoaded('departure')),
            'destination' => new SystemResource($this->whenLoaded('destination')),
            'departs_at' => $this->departs_at,
            'arrives_at' => $this->arrives_at,
            'carrier' => new FleetCarrierResource($this->whenLoaded('carrier')),
            'status' => [
                'cancelled' => !!$this->is_cancelled,
                'boarding' => !!$this->is_boarding,
                'departed' => !!$this->has_departed,
                'departed_at' => !!$this->departed_at,
                'arrived' => !!$this->has_arrived,
                'arrived_at' => !!$this->arrived_at
            ],
            'slug' => $this->slug
        ];
    }
}
