<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemResource extends JsonResource
{
    /**
     * @var bool
     */
    private bool $includeExtended;

    /**
     * Constructor
     */
    public function __construct(Model $model, bool $includeExtended = false)
    {
        parent::__construct($model);
        $this->includeExtended = $includeExtended;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $response = [
            'id' => $this->id,
            'name' => $this->name,
            'updated_at' => $this->updated_at
        ];

        if ($this->includeExtended) {
            $response = array_merge($response, [
                'id64'=> $this->id64,
                'coords' => json_decode($this->coords),
                'information' => new SystemInformationResource($this->whenLoaded('information')),
            ]);
        }

        return $response;
    }
}
