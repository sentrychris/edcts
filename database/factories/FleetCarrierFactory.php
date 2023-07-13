<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FleetCarrier>
 */
class FleetCarrierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'carrier_name' => 'SJEF ' . ucfirst(fake()->firstName()) . ' [VBK-'.rand(100,999).']',
            'has_refuel' => rand(0,1),
            'has_repair' => rand(0,1),
            'has_armory' => rand(0,1),
            'has_shipyard' => rand(0,1),
            'has_outfitting' => rand(0,1),
            'has_cartographics' => rand(0,1),
        ];
    }
}
