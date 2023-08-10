<?php

namespace App\Models;

use App\Libraries\EliteAPIManager;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemBody extends Model
{
    use HasFactory;

    protected $table = 'systems_bodies';

    protected $fillable = [
        'id64',
        'body_id',
        'name',
        'discovered_by',
        'discovered_at',
        'type',
        'sub_type',
        'distance_to_arrival',
        'is_main_star',
        'is_scoopable',
        'spectral_class',
        'luminosity',
        'solar_masses',
        'solar_radius',
        'absolute_magnitude',
        'surface_temp',
        'radius',
        'gravity',
        'earth_masses',
        'atmosphere_type',
        'volcanism_type',
        'terraforming_state',
        'is_landable',
        'orbital_period',
        'orbital_eccentricity',
        'orbital_inclination',
        'arg_of_periapsis',
        'rotational_period',
        'is_tidally_locked',
        'semi_major_axis',
        'axial_tilt',
        'rings',
        'parents'
    ];

    public $timestamps = false;

    /**
     * System relation
     */
    public function system(): BelongsTo {
        return $this->belongsTo(System::class);
    }

    /**
     * Fetch rings as array
     */
    protected function rings(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? json_decode($value) : null
        );
    }

    /**
     * Fetch parents as array
     */
    protected function parents(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? json_decode($value) : null
        );
    }

    public static function checkAPI(System $system)
    {
        $api = app(EliteAPIManager::class);

        if (!$system->bodies()->exists()) {
            $response = $api->setConfig(config('elite.edsm'))
                ->setCategory('system')
                ->get(key: 'bodies', params: [
                    'systemName' => $system->name
                ]);

            $bodies = $response->bodies;

            if ($bodies) {
                foreach($bodies as $body) {
                    $id = random_int(100000000, 999999999);
                    $bodyId = $id;
                    if (property_exists($body,'id64') && $body->id64) {
                        $id = $body->id64;
                        $bodyId = $body->bodyId;
                    }
                    
                    $system->bodies()->updateOrCreate([
                        'id64' => $id,
                        'body_id' => $bodyId,
                        'name' => $body->name,
                        'discovered_by' => $body->discovery->commander,
                        'discovered_at' => $body->discovery->date,
                        'type' => $body->type,
                        'sub_type' => $body->subType,
                        'distance_to_arrival' => property_exists($body, 'distanceToArrival') ? $body->distanceToArrival : null,
                        'is_main_star' => property_exists($body, 'isMainStar') ? $body->isMainStar : false,
                        'is_scoopable' => property_exists($body, 'isScoopable') ? $body->isScoopable : false,
                        'spectral_class' => property_exists($body, 'spectralClass') ? $body->spectralClass : null,
                        'luminosity' => property_exists($body, 'luminosity') ? $body->luminosity : null,
                        'solar_masses' => property_exists($body, 'solarMasses') ? $body->solarMasses : null,
                        'solar_radius' => property_exists($body, 'solarRadius') ? $body->solarRadius : null,
                        'absolute_magnitude' => property_exists($body, 'absoluteMagnitude') ? $body->absoluteMagnitude : null,
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
                        'rings' => property_exists($body, 'rings') ? json_encode($body->rings) : null,
                        'parents' => property_exists($body, 'parents') ? json_encode($body->parents) : null,
                    ]);
                }
            }
        }

        return $system;
    }
}
