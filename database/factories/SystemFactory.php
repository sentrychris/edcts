<?php

namespace Database\Factories;

use App\Models\System;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<System>
 */
class SystemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $x = $this->faker->randomFloat(2, -500, 500);
        $y = $this->faker->randomFloat(2, -500, 500);
        $z = $this->faker->randomFloat(2, -500, 500);

        return [
            'id64' => $this->faker->unique()->numberBetween(1000000, 99999999),
            'name' => $this->faker->unique()->word().' '.$this->faker->randomNumber(4),
            'coords' => json_encode(['x' => $x, 'y' => $y, 'z' => $z]),
            'body_count' => null,
            'updated_at' => now(),
        ];
    }

    /**
     * Place the system at specific coordinates.
     */
    public function atCoords(float $x, float $y, float $z): static
    {
        return $this->state(['coords' => json_encode(['x' => $x, 'y' => $y, 'z' => $z])]);
    }
}
