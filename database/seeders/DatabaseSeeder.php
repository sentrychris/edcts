<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Fake users, commanders
        // Deprecated since being granted access to Frontier OAuth.
        $num = 100;
        // \App\Models\User::factory($num)->create();
        // \App\Models\Commander::factory($num)->create();

        $this->call(StationServiceSeeder::class);
        $this->call(ShipyardTableSeeder::class);
    }
}
