<?php

namespace Database\Factories;

use App\Models\FleetCarrier;
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
        $fleetCarrierIds = FleetCarrier::all()
            ->pluck('id')
            ->toArray();

        $departureSystems = [
            'Merope',
            'Diaguandri',
            'Coriccha',
            'HIP 36601',
            'Sagittarius A*',
            'Voqooe BI-H D11-846'
        ];

        $arrivalSystems = [
            'Colonia',
            'Rohini',
            'Skaude AA-A H294',
            'Sagittarius A*',
            'Beagle Point',
            'Eocs Aub AA-A E9'
        ];

        $carrier = $fleetCarrierIds[array_rand($fleetCarrierIds)];
        $departure = $departureSystems[array_rand($departureSystems)];
        $destination = $arrivalSystems[array_rand($arrivalSystems)];

        $departsAt = Carbon::today()->addDays(rand(1, 90))
            ->addHours(rand(0, 23))
            ->addMinutes(rand(0, 59));

        return [
            'fleet_carrier_id' => $carrier,
            'departure' => $departure,
            'destination' => $destination,
            'title' => $departure . ' > ' . $destination . ' | ' . $departsAt->format('d F \'y H:i') . ' UTC',
            'description' => fake()->paragraphs(2, true),
            'departs_at' => $departsAt->toDateTimeString()
        ];
    }
}
