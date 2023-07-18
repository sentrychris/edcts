<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\System;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $num = 100;

        \App\Models\User::factory($num)->create();
        \App\Models\Commander::factory($num)->create();
        \App\Models\FleetCarrier::factory($num)->create();

        if (System::count() > 2) {
            \App\Models\FleetSchedule::factory($num)->create();
        }
    }
}
