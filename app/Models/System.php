<?php

namespace App\Models;

use App\Libraries\EliteAPIManager;
use App\Traits\HasQueryFilter;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class System extends Model
{
    use HasFactory, HasQueryFilter, Sluggable, SluggableScopeHelpers, SoftDeletes;
    
    protected $table = 'systems';
    
    protected $fillable = [
        'id64',
        'name',
        'coords',
        'updated_at'
    ];
    
    public $timestamps = false;
    
    /**
     * System information relation
     */
    public function information(): HasOne
    {
        return $this->hasOne(SystemInformation::class);
    }

    /**
     * Systemn bodies relation
     */
    public function bodies(): HasMany {
        return $this->hasMany(SystemBody::class);
    }

    /**
     * System departures relation
     */
    public function departures(): HasMany
    {
        return $this->hasMany(FleetSchedule::class, 'departure_system_id');
    }

    /**
     * System arrivals relation
     */
    public function arrivals(): HasMany
    {
        return $this->hasMany(FleetSchedule::class, 'destination_system_id');
    }
    
    /**
    * Filter scope
    */
    public function scopeFilter(Builder $builder, array $options, bool $exact): Builder
    {
        if (!empty($options['search'])) {
            $builder->search($options['search']);
        }
        
        return $this->buildFilterQuery($builder, $options, [
            'name'
        ], $exact);
    }

    /**
     * import from API
     * 
     * @param string $slug
     * 
     * @return System|false
     */
    public static function checkAPI(string $slug)
    {
        $api = app(EliteAPIManager::class);
        $response = $api->setConfig(config('elite.edsm'))
            ->setCategory('systems')
            ->get('system', [
                'systemName' => $slug,
                'showCoordinates' => true,
                'showInformation' => true,
                'showId' => true
            ]);

        if ($response) {
            $system = System::create([
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
     * Check for system information
     */
    public function checkAPIForSystemInformation()
    {
        $api = app(EliteAPIManager::class);
        if (!$this->information()->exists()) {
            $response = $api->setConfig(config('elite.edsm'))
                ->setCategory('systems')
                ->get('system', [
                    'systemName' => $this->name,
                    'showInformation' => true
                ]);

            if ($response->information) {
                $data = [];
                $api->convertResponse($response->information, $data);
                $this->information()->updateOrCreate($data);
            }
        }

        return $this;
    }

    /**
     * Check for system bodies
     */
    public function checkAPIForSystemBodies()
    {
        $api = app(EliteAPIManager::class);
        if (!$this->bodies()->exists()) {
            $response = $api->setConfig(config('elite.edsm'))
                ->setCategory('system')
                ->get('bodies', [
                    'systemName' => $this->name
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
                    
                    $this->bodies()->updateOrCreate([
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

        return $this;
    }

    /**
     * configure slug
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['id64', 'name'],
                'separator' => '-'
            ]
        ];
    }
}
