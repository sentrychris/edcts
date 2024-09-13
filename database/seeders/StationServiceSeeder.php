<?php

namespace Database\Seeders;

use App\Models\StationService;
use Illuminate\Database\Seeder;

class StationServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StationService::updateOrCreate(
            ['name' => 'Universal Cartographics'],
            ['description' => 'Universal Cartographics is a Mega-Corporation focused on mapping the galaxy. Cynthia Sideris is the current Chair of Universal Cartographics..']
        );

        StationService::updateOrCreate(
            ['name' => 'Vista Genomics'],
            ['description' => 'Vista Genomics is an exobiology company that will buy the genetic information of alien life forms. It offers its services at Concourses.']
        );

        StationService::updateOrCreate(
            ['name' => 'Pioneer Supplies'],
            ['description' => 'Pioneer Supplies is a general store chain found at Concourses. They offer a wide variety of purchasable handheld weapons, suits, and consumables for commanders.']
        );

        StationService::updateOrCreate(
            ['name' => 'Apex Interstellar Transport'],
            ['description' => 'Apex Interstellar Transport is a shuttle service that allows commanders to travel between most starports and settlements without the need to operate their own ship.']
        );

        StationService::updateOrCreate(
            ['name' => 'Frontline Solutions'],
            ['description' => 'Frontline Solutions is a non-aligned mercenary company that offers a selection of Conflict Zone missions. These missions do not influence a Commander\'s standing.']
        );

        StationService::updateOrCreate(
            ['name' => 'Search and Rescue'],
            ['description' => 'Search and Rescue is a service that allows commanders to provide assistance, conduct missions and turn in legal salvage items to agent contacts for rewards.']
        );

        StationService::updateOrCreate(
            ['name' => 'Contacts'],
            ['description' => 'Contacts are the people who provide various services and missions to commanders at starports and settlements. They can be found in the Contacts menu.']
        );

        StationService::updateOrCreate(
            ['name' => 'Crew Lounge'],
            ['description' => 'The Crew Lounge is a service that allows commanders to hire NPC crew members to serve on their ships and assist in missions or defence during exploration.']
        );
    }
}
