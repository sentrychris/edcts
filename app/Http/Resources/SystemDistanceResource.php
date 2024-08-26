<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemDistanceResource extends JsonResource
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
            'information' => new SystemInformationResource($this->whenLoaded('information')),
            'coords' => json_decode($this->coords),
            'distance' => $this->distance,
            'slug' => $this->slug,
        ];
    }
}
