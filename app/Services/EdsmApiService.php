<?php

namespace App\Services;

use Exception;
use App\Models\System;
use Illuminate\Support\Facades\Log;

class EdsmApiService extends ApiService
{
    /**
     * Search EDSM for system data by system name and update records if found.
     * 
     * @param string $name - the system name
     * @return mixed the created system record or false
     */
    public function updateSystemData(string $name): System|bool
    {
        $system = false;

        // Split the string in case it's a slug prefixed with the id64
        $parts = explode('-', $name, 2);
        $systemName = $parts[1];
    
        try {
            // Set the API config and make the request
            $response = $this->setConfig(config('elite.edsm'))
                ->setCategory('systems')
                ->get(key: 'system', params: [
                    'systemName' => $systemName,
                    'showCoordinates' => true,
                    'showInformation' => true,
                    'showId' => true
                ]);

            // If there is a successful response, update the system record
            if ($response) {
                $system = System::updateOrCreate(['id64' => $response->id64], [
                    'id64' => $response->id64,
                    'name' => $response->name,
                    'coords' => json_encode($response->coords),
                    'updated_at' => now()
                ]);
            } else {
                Log::channel('import:system')
                    ->error('updateSystemDataError: No response from EDSM.');
            }
        } catch (Exception $e) {
            Log::channel('import:system')
                ->error('updateSystemDataError: ' . $e->getMessage());
        }

        return $system;
    }

    /**
     * Search EDSM for system bodies data by system name and update records if found.
     * 
     * @param System $system - the system we are searching bodies for
     */
    public function updateSystemBodiesData(System $system): void
    {
        $response = $this->setConfig(config('elite.edsm'))
            ->setCategory('system')
            ->get(key: 'bodies', params: [
                'systemName' => $system->name
            ]);

        if ($response) {
            $bodies = property_isset($response, 'bodies')
                ? $response->bodies
                : (isset($response['bodies']) ? $response['bodies'] : []);

            $system->body_count = property_isset($response, 'bodyCount')
                ? $response->bodyCount
                : null;

            $system->save();

            if ($bodies) {
                foreach($bodies as $body) {
                    try {
                        $id = random_int(100000000, 999999999);
                        $bodyId = $id;

                        if (property_isset($body,'id64')) {
                            $id = $body->id64;
                            $bodyId = $body->bodyId;
                        }
                        
                        $system->bodies()->updateOrCreate(['id64' => $id], [
                            'id64' => $id,
                            'body_id' => $bodyId,
                            'name' => $body->name,
                            'type' => $body->type,
                            'sub_type' => $body->subType,

                            'discovered_by' => property_isset($body, 'discovery')
                                ? $body->discovery->commander
                                : null,

                            'discovered_at' => property_isset($body, 'discovery')
                                ? $body->discovery->date
                                : null,

                            'distance_to_arrival' => property_isset($body, 'distanceToArrival')
                                ? $body->distanceToArrival
                                : null,

                            'is_main_star' => property_isset($body, 'isMainStar')
                                ? $body->isMainStar
                                : false,

                            'is_scoopable' => property_isset($body, 'isScoopable')
                                ? $body->isScoopable
                                : false,

                            'spectral_class' => property_isset($body, 'spectralClass')
                                ? $body->spectralClass
                                : null,

                            'luminosity' => property_isset($body, 'luminosity')
                                ? $body->luminosity
                                : null,

                            'solar_masses' => property_isset($body, 'solarMasses')
                                ? $body->solarMasses
                                : null,

                            'solar_radius' => property_isset($body, 'solarRadius')
                                ? $body->solarRadius
                                : null,

                            'absolute_magnitude' => property_isset($body, 'absoluteMagnitude')
                                ? $body->absoluteMagnitude
                                : null,

                            'surface_temp' => property_isset($body, 'surfaceTemperature')
                                ? $body->surfaceTemperature
                                : null,

                            'radius' => property_isset($body, 'radius')
                                ? $body->radius
                                : null,

                            'gravity' => property_isset($body, 'gravity')
                                ? $body->gravity
                                : null,

                            'earth_masses' => property_isset($body, 'earthMasses')
                                ? $body->earthMasses
                                : null,

                            'atmosphere_type' => property_isset($body, 'atmosphereType')
                                ? $body->atmosphereType
                                : null,

                            'volcanism_type' => property_isset($body, 'volcanismType')
                                ? $body->volcanismType
                                : null,

                            'terraforming_state' => property_isset($body, 'terraformingState')
                                ? $body->terraformingState
                                : null,

                            'is_landable' => property_isset($body, 'isLandable')
                                ? $body->isLandable
                                : false,

                            'orbital_period' => property_isset($body, 'orbitalPeriod')
                                ? $body->orbitalPeriod
                                : null,

                            'orbital_eccentricity' => property_isset($body, 'orbitalEccentricity')
                                ? $body->orbitalEccentricity
                                : null,

                            'orbital_inclination' => property_isset($body, 'orbitalInclination')
                                ? $body->orbitalInclination
                                : null,

                            'arg_of_periapsis' => property_isset($body, 'argOfPeriapsis')
                                ? $body->argOfPeriapsis
                                : null,

                            'rotational_period' => property_isset($body, 'rotationalPeriod')
                                ? $body->rotationalPeriod
                                : null,

                            'is_tidally_locked' => property_isset($body, 'rotationalPeriodTidallyLocked')
                                ? $body->rotationalPeriodTidallyLocked
                                : false,

                            'semi_major_axis' => property_isset($body, 'semiMajorAxis')
                                ? $body->semiMajorAxis
                                : null,

                            'axial_tilt' => property_isset($body, 'axialTilt')
                                ? $body->axialTilt
                                : null,

                            'rings' => property_isset($body, 'rings')
                                ? json_encode($body->rings)
                                : null,
                            
                            'parents' => property_isset($body, 'parents')
                                ? json_encode($body->parents)
                                : null,
                        ]);
                    } catch (Exception $e) {
                        Log::channel('import:system')
                            ->error('updateSystemBodiesDataError: ' . $e->getMessage());
                    }
                }
            }
        } else {
            Log::channel('import:system')
                ->error('updateSystemBodiesDataError: No response from EDSM.');
        }
    }

    /**
     * Search EDSM for system information data by system name and update records if found.
     * 
     * @param System $system - the system we are searching information for
     * @return void
     */
    public function updateSystemInformationData(System $system): void
    {
        $response = $this->setConfig(config('elite.edsm'))
            ->setCategory('systems')
            ->get(key: 'system', params: [
                'systemName' => $system->name,
                'showInformation' => true
            ]);

        if ($response && property_isset($response, 'information')) {
            try {
                $information = $response->information;

                $system->information()->updateOrCreate([
                    'allegiance' => property_isset($information, 'allegiance')
                        ? $information->allegiance
                        : null,

                    'government' => property_isset($information, 'government')
                        ? $information->government
                        : null,

                    'faction' => property_isset($information, 'faction')
                        ? $information->faction
                        : null,

                    'faction_state' => property_isset($information, 'factionState')
                        ? $information->factionState
                        : null,

                    'economy' => property_isset($information, 'economy')
                        ? $information->economy
                        : null,

                    'population' => property_isset($information, 'population')
                        ? $information->population
                        : 0,

                    'security' => property_isset($information, 'security')
                        ? $information->security
                        : null,
                ]);
            } catch (Exception $e) {
                Log::channel('import:system')
                    ->error('updateSystemInformationDataError: ' . $e->getMessage());
            }
        } else {
            Log::channel('import:system')
                ->error('updateSystemInformationDataError: No response from EDSM.');
        }
    }

    /**
     * Search EDSM for system stations data by system name and update records if found.
     * 
     * @param System $system - the system we are searching stations for
     * @return void
     */
    public function updateSystemStationsData(System $system): void
    {
        $response = $this->setConfig(config('elite.edsm'))
            ->setCategory('system')
            ->get(key: 'stations', subkey: 'stations', params:[
                'systemName' => $system->name,
                'showId' => true
            ]);

        if ($response && property_isset($response, 'stations')) {
            $stations = $response->stations;

            foreach ($stations as $station) {
                try {
                    $station = $system->stations()->updateOrCreate(
                        [
                            'name' => $station->name,
                            'type' => $station->type,
                        ],
                        [
                            'market_id' => property_isset($station, 'marketId')
                                ? $station->marketId
                                : null,

                            'distance_to_arrival' => property_isset($station, 'distanceToArrival')
                                ? $station->distanceToArrival
                                : null,

                            'body' => property_isset($station, 'body')
                                ? json_encode($station->body)
                                : null,

                            'allegiance' => property_isset($station, 'allegiance')
                                ? $station->allegiance
                                : null,

                            'government' => property_isset($station, 'government')
                                ? $station->government
                                : null,

                            'economy' => property_isset($station, 'economy')
                                ? $station->economy
                                : null,

                            'second_economy' => property_isset($station, 'secondEconomy')
                                ? $station->secondEconomy
                                : null,

                            'has_market' => property_isset($station, 'haveMarket')
                                ? $station->haveMarket
                                : null,

                            'has_shipyard' => property_isset($station, 'haveShipyard')
                                ?  $station->haveShipyard
                                : null,

                            'has_outfitting' => property_isset($station, 'haveOutfitting')
                                ? $station->haveOutfitting
                                : null,

                            'other_services' => is_array($station->otherServices)
                                ? implode(',', $station->otherServices)
                                : null,

                            'controlling_faction' => property_isset($station, 'controllingFaction')
                                ? $station->controllingFaction->name
                                : null,

                            'information_last_updated' => property_isset($station, 'updateTime')
                                ? $station->updateTime->information
                                : null,

                            'market_last_updated' => property_isset($station, 'updateTime')
                                ? $station->updateTime->market
                                : null,

                            'shipyard_last_updated' => property_isset($station, 'updateTime')
                                ? $station->updateTime->shipyard
                                : null,

                            'outfitting_last_updated' => property_isset($station, 'updateTime')
                                ? $station->updateTime->outfitting
                                : null,
                        ]
                    );
                } catch (Exception $e) {
                    Log::channel('import:system')
                        ->error('updateSystemsStationDataError: ' . $e->getMessage());
                }
            }
        } else {
            Log::channel('import:system')
                ->error('updateSystemsStationDataError: No response from EDSM.');
        }
    }
}