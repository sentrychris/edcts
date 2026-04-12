<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemRouteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'jump' => $this->resource['jump'],
            'id' => $this->resource['system']->id,
            'id64' => $this->resource['system']->id64,
            'name' => $this->resource['system']->name,
            'coords' => ['x' => $this->resource['system']->coords_x, 'y' => $this->resource['system']->coords_y, 'z' => $this->resource['system']->coords_z],
            'slug' => $this->resource['system']->slug,
            'distance' => round($this->resource['distance'], 2),
            'total_distance' => round($this->resource['total_distance'], 2),
        ];
    }
}
