<?php

namespace Database\Factories;

use App\Models\FleetCarrier;
use App\Models\System;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FleetSchedule>
 */
class FleetScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $carrier = FleetCarrier::inRandomOrder()->first();
        $departure = System::inRandomOrder()->first();
        $destination = System::inRandomOrder()->first();

        $departsAt = Carbon::today()->addDays(rand(1, 90))->addHours(rand(0, 23))->addMinutes(rand(0, 59));
        $isBoarding = $departsAt->diffInDays(now()) <= 2;
        $isCancelled = !$isBoarding ? rand(0,1) : 0;

        return [
            'fleet_carrier_id' => $carrier->id,
            'departure_system_id' => $departure->id,
            'destination_system_id' => $destination->id,
            'title' => $departure->name . ' > ' . $destination->name . ' | ' . $departsAt->format('d F \'y H:i') . ' UTC',
            'description' => fake()->paragraphs(2, true),
            'departs_at' => $departsAt->toDateTimeString(),
            'is_boarding' => $isBoarding,
            'is_cancelled' => $isCancelled
        ];
    }
}
