<?php

namespace App\Services;

use App\Models\System;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EdsmApiService extends ApiService
{
    /**
     * Search EDSM for system data by system name and update records if found.
     * 
     * @param string $name - the system name
     * @return mixed the created system record or false
     */
    public function updateSystemData(string $name): mixed
    {
        // Split the string in case it's a slug prefixed with the id64
        $parts = explode('-', $name, 2);
        $systemName = $parts[1];
    
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
        }

        if (! $system) {
            return false;
        }

        return $system;
    }

    /**
     * Search EDSM for system bodies data by system name and update records if found.
     * 
     * @param System $system - the system we are searching bodies for
     * @return System - the updated system record
     */
    public function updateSystemBodiesData(System $system) {
        if (!$system->bodies()->exists() && $system->body_count === null) {
            $response = $this->setConfig(config('elite.edsm'))
                ->setCategory('system')
                ->get(key: 'bodies', params: [
                    'systemName' => $system->name
                ]);

            $bodies = property_isset($response, 'bodies')
                ? $response->bodies
                : (isset($response['bodies']) ? $response['bodies'] : []);

            $system->body_count = $response->bodyCount ?? 0;
            $system->save();

            if ($bodies) {
                foreach($bodies as $body) {
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
                        'discovered_by' => $body->discovery->commander,
                        'discovered_at' => $body->discovery->date,
                        'type' => $body->type,
                        'sub_type' => $body->subType,
                        'distance_to_arrival' => property_isset($body, 'distanceToArrival') ? $body->distanceToArrival : null,
                        'is_main_star' => property_isset($body, 'isMainStar') ? $body->isMainStar : false,
                        'is_scoopable' => property_isset($body, 'isScoopable') ? $body->isScoopable : false,
                        'spectral_class' => property_isset($body, 'spectralClass') ? $body->spectralClass : null,
                        'luminosity' => property_isset($body, 'luminosity') ? $body->luminosity : null,
                        'solar_masses' => property_isset($body, 'solarMasses') ? $body->solarMasses : null,
                        'solar_radius' => property_isset($body, 'solarRadius') ? $body->solarRadius : null,
                        'absolute_magnitude' => property_isset($body, 'absoluteMagnitude') ? $body->absoluteMagnitude : null,
                        'surface_temp' => $body->surfaceTemperature,
                        'radius' => $body->radius ?? null,
                        'gravity' => $body->gravity ?? null,
                        'earth_masses' => $body->earthMasses ?? null,
                        'atmosphere_type' => $body->atmosphereType ?? null,
                        'volcanism_type' => $body->volcanismType ?? null,
                        'terraforming_state' => $body->terraformingState ?? null,
                        'is_landable' => $body->isLandable ?? false,
                        'orbital_period' => $body->orbitalPeriod ?? null,
                        'orbital_eccentricity' => $body->orbitalEccentricity ?? null,
                        'orbital_inclination' => $body->orbitalInclination ?? null,
                        'arg_of_periapsis' => $body->argOfPeriapsis ?? null,
                        'rotational_period' => $body->rotationalPeriod ?? null,
                        'is_tidally_locked' => $body->rotationalPeriodTidallyLocked ?? false,
                        'semi_major_axis' => $body->semiMajorAxis ?? null,
                        'axial_tilt' => $body->axialTilt ?? null,
                        'rings' => property_isset($body, 'rings') ? json_encode($body->rings) : null,
                        'parents' => property_isset($body, 'parents') ? json_encode($body->parents) : null,
                    ]);
                }
            }
        }

        return $system;
    }

    /**
     * Search EDSM for system information data by system name and update records if found.
     * 
     * @param System $system - the system we are searching information for
     * @return System - the updated system record
     */
    public function updateSystemInformationData(System $system) {
        if (!$system->information()->exists()) {

            $response = $this->setConfig(config('elite.edsm'))
                ->setCategory('systems')
                ->get(key: 'system', params: [
                    'systemName' => $system->name,
                    'showInformation' => true
                ]);

            if ($response->information) {
                $data = [];
                $this->convertResponse($response->information, $data);

                $fillable = (new System())->getFillable();

                $data = array_filter($data, function($v, $k) use ($fillable) {
                    return in_array($k, $fillable);
                }, ARRAY_FILTER_USE_BOTH);

                $system->information()->updateOrCreate($data);
            }
        }

        return $system;
    }

    /**
     * Make call to Elite API
     * 
     * @param string $key
     * @param ?string $subkey
     * @param ?array $params
     * 
     * @return mixed
     */
    public function get(string $key, ?string $subkey = null, ?array $params = null): mixed
    {
        $url = $this->config['base_url']
            . $this->resolveUri($this->category, $key, $subkey)
            . $this->buildQueryString($params);

        $response = Http::withHeaders($this->headers)->get($url);
        $status = $response->getStatusCode();

        if ($status !== 200) {
            Log::channel('thirdparty')->error('API call failed', [
                'status' => $status,
                'reason' => $response->getReasonPhrase(),
                'url' => $url,
                'config' => $this->config,
            ]);
        }


        return $this->getContents($response, true);
    }
    
    /**
     * Resolve uri from config
     * 
     * @param string $section
     * @param string $key
     * @param ?string $subKey
     * 
     * @return string|false
     */
    public function resolveUri(
        string $section,
        string $key,
        string $subKey = null
    ): string|false {
        $section = $this->config[$section];
        if ($section && $section[$key]) {

            if (is_array($section[$key]) && $subKey && $section[$key][$subKey]) {

                return $section[$key][$subKey];
            }

            return $section[$key];
        }

        return false;
    }

    /**
     * Convert elite API response
     * 
     * @param mixed $obj,
     * @param mixed &$arr
     * 
     * @return mixed
     */
    public function convertResponse($obj, &$arr): mixed
    {
        if (!is_object($obj) && !is_array($obj)) {
            $arr = $obj;
            return $arr;
        }
        
        foreach ($obj as $key => $value){
            $key = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));

            if (!empty($value)) {
                $arr[$key] = array();
                $this->convertResponse($value, $arr[$key]);
            } else {
                $arr[$key] = $value;
            }
        }
        
        return $arr;
    }

    /**
     * Build query string for request
     * 
     * @param ?array $params
     * 
     * @return string
     */
    private function buildQueryString(?array $params = null): string
    {
        if (!$params) {
            return '';
        }

        $i = 0;
        $template = '';
        foreach ($params as $k => $v) {
            $template .= ($i === 0 ? '?' : '&') . $k . '=' . $v;
            ++$i;
        }

        return $template;
    }
}