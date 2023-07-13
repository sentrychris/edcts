<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommanderResource extends JsonResource
{
    /**
     * @var bool
     */
    private bool $includeAuth = false;

    /**
     * Constructor
     */
    public function __construct(mixed $resource, bool $includeAuth = false)
    {
        if ($includeAuth) {
            $this->includeAuth = true;
        }

        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = [
            'name' => $this->cmdr_name,
            'carriers' => FleetCarrierResource::collection($this->whenLoaded('carriers'))
        ];

        if ($this->includeAuth) {
            $resource['api'] = [
                'edsm' => $this->edsm_api_key,
                'inara' => $this->inara_api_key
            ];
        }

        return $resource;
    }
}
