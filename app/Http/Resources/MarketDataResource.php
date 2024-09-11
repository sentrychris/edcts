<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource === null) {
            return [];
        }

        return [
            'station' => $this->station,
            'system' => $this->system,
            'commodities' => $this->parseCommodities($this->commodities),
            'prohibited' => $this->prohibited,
            'last_updated' => $this->last_updated
        ];
    }

    /**
     *  Parse the commodities array.
     * 
     * @param array $commodities
     * @return array
     */
    private function parseCommodities(array $commodities): array
    {
        $parsed = [];

        foreach ($commodities as $commodity) {
            $name = $commodity['name'];
            unset($commodity['name']);
            $parsed[$name] = $commodity;
        }
    
        return $parsed;
    }
}
