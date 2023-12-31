<?php

namespace Database\Factories;

use App\Models\Commander;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FleetCarrier>
 */
class FleetCarrierFactory extends Factory
{
    /**
     * Unique identifiers
     */
    private $usedIdentifiers = [];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $commanderIds = Commander::all()->pluck('id')->toArray();

        return [
            'name' => ucfirst(fake()->firstName()) . ' ' . ucfirst(fake()->lastName()),
            'commander_id' => $commanderIds[array_rand($commanderIds)],
            'identifier' => $this->generateUniqueIdentifier(),
            'has_refuel' => rand(0,1),
            'has_repair' => rand(0,1),
            'has_armory' => rand(0,1),
            'has_shipyard' => rand(0,1),
            'has_outfitting' => rand(0,1),
            'has_cartographics' => rand(0,1),
        ];
    }

    private function generateUniqueIdentifier()
    {
        $rand = '';
        $seed = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        shuffle($seed);
        foreach (array_rand($seed, 3) as $k) {
            $rand .= $seed[$k];
        }

        $identifier = $rand . '-' . rand(100,999);

        if (!(in_array($identifier, $this->usedIdentifiers))) {
            $this->usedIdentifiers[] = $identifier;
            return $identifier;
        }

        $this->generateIdentifier();
    }
}
