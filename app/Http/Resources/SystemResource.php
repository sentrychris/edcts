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
            'name' => $this->name,
            'coords' => json_decode($this->coords),
            'main_star' => $this->main_star,
            'updated_at' => $this->updated_at,
            'information' => $this->whenLoaded('information')
        ];
    }
}
