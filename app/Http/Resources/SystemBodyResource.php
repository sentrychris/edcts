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
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'sub_type' => $this->sub_type,
            'discovery' => [
                'commander' => $this->discovered_by,
                'date' => $this->discovered_at
            ],
            'radius' => $this->radius,
            'gravity' => $this->gravity,
            'earth_masses' => $this->earth_masses,
            'surface_temp' => $this->surface_temp,
            'is_landable' => $this->is_landable,
            'atmosphere_type' => $this->atmosphere_type,
            'volcanism_type' => $this->volcanism_type,
            'terraforming_state' => $this->terraforming_state,
            'axial' => [
                'axial_tilt' => $this->axial_tilt,
                'semi_major_axis' => $this->semi_major_axis,
                'rotational_period' => $this->rotational_period
            ],
            'orbital' => [
                'orbital_period' => $this->orbital_period,
                'orbital_eccentricity' => $this->orbital_eccentricity,
                'orbital_inclination' => $this->orbital_inclination,
                'arg_of_periapsis' => $this->arg_of_periapsis
            ],
            'is_tidally_locked' => $this->is_tidally_locked,
            'rings' => json_decode($this->rings),
            'parents' => json_decode($this->parents)
        ];
    }
}
