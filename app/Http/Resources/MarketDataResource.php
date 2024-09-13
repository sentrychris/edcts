<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource === null) {
            return [];
        }

        return [
            'station' => $this->station,
            'system' => $this->system,
            'commodities' => $this->parseCommodities($this->commodities),
            'prohibited' => $this->prohibited,
            'last_updated' => $this->last_updated
        ];
    }

    /**
     *  Parse the commodities array.
     * 
     * @param array $commodities
     * @return array
     */
    private function parseCommodities(array $commodities): array
    {
        $parsed = [];

        foreach ($commodities as $commodity) {
            $name = $commodity->name;
            $commodity->name = $this->mapCommodityDisplayNames($name);
            $parsed[$name] = $commodity;
        }

        return $parsed;
    }

    private function mapCommodityDisplayNames(string $commodity)
    {
        $map = [
            'anynacoffee' => 'Anyna Coffee',
            'agriculturalmedicines' => 'Agricultural Medicines',
            'alacarakmoskinart' => 'Alacarakmo Skin Art',
            'albinoquechuamammoth' => 'Albino Quechua Mammoth',
            'animalmonitors' => 'Animal Monitors',
            'aquaponicsystems' => 'Aquaponic Systems',
            'autofabricators' => 'Auto Fabricators',
            'atmosphericextractors' => 'Atmospheric Extractors',
            'advancedcatalysers' => 'Advanced Catalysers',
            'advancedmedicines' => 'Advanced Medicines',
            'agronomictreatment' => 'Agronomic Treatment',
            'alexandrite' => 'Alexandrite',
            'aluminium' => 'Aluminium',
            'animalmeat' => 'Animal Meat',
            'baltahsinevacuumkrill' => 'Baltah\'sine Vacuum Krill',
            'basicnarcotics' => 'Basic Narcotics',
            'basicmedicines' => 'Basic Medicines',
            'bauxite' => 'Bauxite',
            'beer' => 'Beer',
            'benitoite' => 'Benitoite',
            'bertrandite' => 'Bertrandite',
            'beryllium' => 'Beryllium',
            'bioreducinglichen' => 'Bioreducing Lichen',
            'biowaste' => 'Bio Waste',
            'bootlegliquor' => 'Bootleg Liquor',
            'cetirabbits' => 'Ceti Rabbits',
            'chemicalwaste' => 'Chemical Waste',
            'clothing' => 'Clothing',
            'cobalt' => 'Cobalt',
            'coffee' => 'Coffee',
            'coltan' => 'Coltan',
            'combatstabilisers' => 'Combat Stabilisers',
            'computercomponents' => 'Computer Components',
            'conductivefabrics' => 'Conductive Fabrics',
            'consumertechnology' => 'Consumer Technology',
            'copper' => 'Copper',
            'cropharvesters' => 'Crop Harvesters',
            'damagedescapepod' => 'Damaged Escape Pod',
            'domesticappliances' => 'Domestic Appliances',
            'eshuumbrellas' => 'Eshu Umbrellas',
            'explosives' => 'Explosives',
            'fish' => 'Fish',
            'foodcartridges' => 'Food Cartridges',
            'fruitandvegetables' => 'Fruit and Vegetables',
            'gallite' => 'Gallite',
            'gallium' => 'Gallium',
            'gold' => 'Gold',
            'goslarite' => 'Goslarite',
            'grain' => 'Grain',
            'grandidierite' => 'Grandidierite',
            'giantirukamasnails' => 'Giant Irukama Snails',
            'hazardousenvironmentsuits' => 'Hazardous Environment Suits',
            'heliostaticfurnaces' => 'Heliostatic Furnaces',
            'hip10175bushmeat' => 'HIP 10175 Bush Meat',
            'hostage' => 'Hostage',
            'hydrogenfuel' => 'Hydrogen Fuel',
            'hydrogenperoxide' => 'Hydrogen Peroxide',
            'karetiicouture' => 'Karetii Couture',
            'karsukilocusts' => 'Karsuki Locusts',
            'kachiriginleaches' => 'Kachirigin Leaches',
            'konggaale' => 'Kongga Ale',
            'indite'=> 'Indite',
            'indium' => 'Indium',
            'insulatingmembrane' => 'Insulating Membrane',
            'lepidolite' => 'Lepidolite',
            'lavianbrandy' => 'Lavian Brandy',
            'liquidoxygen' => 'Liquid Oxygen',
            'liquor' => 'Liquor',
            'lithium' => 'Lithium',
            'lithiumhydroxide' => 'Lithium Hydroxide',
            'lowtemperaturediamond' => 'Low Temperature Diamond',
            'marinesupplies' => 'Marine Supplies',
            'methaneclathrate' => 'Methane Clathrate',
            'militarygradefabrics' => 'Military Grade Fabrics',
            'mineralextractors' => 'Mineral Extractors',
            'mineraloil' => 'Mineral Oil',
            'monzanite' => 'Monzanite',
            'musgravite' => 'Musgravite',
            'njangarisaddles' => 'Njangari Saddles',
            'naturalfabrics' => 'Natural Fabrics',
            'nonlethalweapons' => 'Non-lethal Weapons',
            'occupiedcryopod' => 'Occupied Cryopod',
            'opal' => 'Opal',
            'painite' => 'Painite',
            'performanceenhancers' => 'Performance Enhancers',
            'personaleffects' => 'Personal Effects',
            'personalweapons' => 'Personal Weapons',
            'polymers' => 'Polymers',
            'powergenerators' => 'Power Generators',
            'progenitorcells' => 'Progenitor Cells',
            'pyrophyllite' => 'Pyrophyllite',
            'reactivearmour' => 'Reactive Armour',
            'resonatingseparators' => 'Resonating Separators',
            'rhodplumsite' => 'Rhodplumsite',
            'rutile' => 'Rutile',
            'scrap' => 'Scrap',
            'semiconductors' => 'Semiconductors',
            'serendibite' => 'Serendibite',
            'silver' => 'Silver',
            'superconductors' => 'Superconductors',
            'surfacestabilisers' => 'Surface Stabilisers',
            'syntheticfabrics' => 'Synthetic Fabrics',
            'syntheticmeat' => 'Synthetic Meat',
            'tantalum' => 'Tantalum',
            'tea' => 'Tea',
            'terrainenrichmentsystems' => 'Terrain Enrichment Systems',
            'thargoidpod' => 'Thargoid Pod',
            'thargoidtissuesampletype6' => 'Thargoid Tissue Sample Type 6',
            'thargoidtissuesampletype9a' => 'Thargoid Tissue Sample Type 9A',
            'thargoidtissuesampletype9b' => 'Thargoid Tissue Sample Type 9B',
            'thargoidtissuesampletype9c' => 'Thargoid Tissue Sample Type 9C',
            'titanium' => 'Titanium',
            'tritium' => 'Tritium',
            'unocuppiedescapepod' => 'Unoccupied Escape Pod',
            'uraninite' => 'Uraninite',
            'uranium' => 'Uranium',
            'usscargoblackbox' => 'USS Cargo Black Box',
            'usscargorareartwork' => 'USS Cargo Rare Artwork',
            'utgaroarmillenialeggs' => 'Utgaroar Millennial Eggs',
            'water' => 'Water',
            'waterpurifiers' => 'Water Purifiers',
            'wreckagecomponents' => 'Wreckage Components',
            'witchhaulkobebeef' => 'Witchhaul Kobe Beef',
            'wuthielokufroth' => 'Wuthielo Ku Froth',
        ];

        return $map[$commodity] ?? $commodity;
    }
}
