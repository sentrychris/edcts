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
            'advancedcatalysers' => 'Advanced Catalysers',
            'advancedmedicines' => 'Advanced Medicines',
            'agronomictreatment' => 'Agronomic Treatment',
            'alexandrite' => 'Alexandrite',
            'aluminium' => 'Aluminium',
            'animalmeat' => 'Animal Meat',
            'basicmedicines' => 'Basic Medicines',
            'bauxite' => 'Bauxite',
            'beer' => 'Beer',
            'benitoite' => 'Benitoite',
            'bertrandite' => 'Bertrandite',
            'beryllium' => 'Beryllium',
            'biowaste' => 'Bio Waste',
            'chemicalwaste' => 'Chemical Waste',
            'clothing' => 'Clothing',
            'cobalt' => 'Cobalt',
            'coffee' => 'Coffee',
            'coltan' => 'Coltan',
            'conductivefabrics' => 'Conductive Fabrics',
            'consumertechnology' => 'Consumer Technology',
            'copper' => 'Copper',
            'damagedescapepod' => 'Damaged Escape Pod',
            'domesticappliances' => 'Domestic Appliances',
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
            'hazardousenvironmentsuits' => 'Hazardous Environment Suits',
            'heliostaticfurnaces' => 'Heliostatic Furnaces',
            'hostage' => 'Hostage',
            'hydrogenfuel' => 'Hydrogen Fuel',
            'hydrogenperoxide' => 'Hydrogen Peroxide',
            'indite'=> 'Indite',
            'indium' => 'Indium',
            'insulatingmembrane' => 'Insulating Membrane',
            'lepidolite' => 'Lepidolite',
            'liquidoxygen' => 'Liquid Oxygen',
            'liquor' => 'Liquor',
            'lithium' => 'Lithium',
            'lithiumhydroxide' => 'Lithium Hydroxide',
            'lowtemperaturediamond' => 'Low Temperature Diamond',
            'methaneclathrate' => 'Methane Clathrate',
            'militarygradefabrics' => 'Military Grade Fabrics',
            'mineraloil' => 'Mineral Oil',
            'monzanite' => 'Monzanite',
            'musgravite' => 'Musgravite',
            'nonlethalweapons' => 'Non-lethal Weapons',
            'occupiedcryopod' => 'Occupied Cryopod',
            'opal' => 'Opal',
            'painite' => 'Painite',
            'performanceenhancers' => 'Performance Enhancers',
            'personaleffects' => 'Personal Effects',
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
            'thargoidpod' => 'Thargoid Pod',
            'titanium' => 'Titanium',
            'tritium' => 'Tritium',
            'unoccupiedescapepod' => 'Unoccupied Escape Pod',
            'uraninite' => 'Uraninite',
            'uranium' => 'Uranium',
            'usscargoblackbox' => 'USS Cargo Black Box',
            'water' => 'Water',
            'waterpurifiers' => 'Water Purifiers',
            'wreckagecomponents' => 'Wreckage Components',
        ];

        return $map[$commodity] ?? $commodity;
    }
}
