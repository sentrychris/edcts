<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemBodyResource extends JsonResource
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
            'type' => $this->type,
            'sub_type' => $this->sub_type,
            'discovery' => [
                'commander' => $this->discovered_by,
                'date' => $this->discovered_at
            ]
        ];
    }
}
