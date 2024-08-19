<?php

namespace App\Models;

use App\Services\EdsmApiService;
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
        'body_count',
        'main_star',
        'updated_at',
    ];
    
    public $timestamps = false;
    
    /**
     * System information relation
     */
    public function information(): HasOne
    {
        return $this->hasOne(SystemInformation::class);
    }

    public function getInformationAttribute()
    {        
        $information = $this->getRelationValue('information');

        $this->setRelation('information', $information);

        return $information;
    }

    /**
     * Systemn bodies relation
     */
    public function bodies(): HasMany {
        return $this->hasMany(SystemBody::class);
    }

    public function getBodiesAttribute()
    {        
        $bodies = $this->getRelationValue('bodies');

        $this->setRelation('bodies', $bodies);

        return $bodies;
    }

    /**
     * System stations relation
     */
    public function stations(): HasMany {
        return $this->hasMany(SystemStation::class);
    }

    public function getStationsAttribute()
    {        
        $stations = $this->getRelationValue('stations');

        $this->setRelation('stations', $stations);

        return $stations;
    }

    /**
     * System departures relation
     */
    public function departures(): HasMany
    {
        return $this->hasMany(FleetSchedule::class, 'departure_system_id');
    }

    public function getDeparturesAttribute()
    {        
        $departures = $this->getRelationValue('departures');

        $this->setRelation('departures', $departures);

        return $departures;
    }

    /**
     * System arrivals relation
     */
    public function arrivals(): HasMany
    {
        return $this->hasMany(FleetSchedule::class, 'destination_system_id');
    }

    public function getArrivalsAttribute()
    {        
        $arrivals = $this->getRelationValue('arrivals');

        $this->setRelation('arrivals', $arrivals);

        return $arrivals;
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

    public function getCacheKey(string $type) {
        return sprintf('system:%d:'.$type, $this->id64);
    }

    /**
     * import from API
     * 
     * @param string $slug
     * 
     * @return System|false
     */
    public static function retrieveBy(string $slug)
    {
        $api = app(EdsmApiService::class);
        $response = $api->setConfig(config('elite.edsm'))
            ->setCategory('systems')
            ->get(key: 'system', params: [
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
     * Get update time according to various 3rd party formats.
     */
    public static function getAPIUpdateTime($system): mixed
    {
        // Spansh dumps
        if (property_isset($system, 'updateTime')
            && is_string($system->updateTime)
            && $system->updateTime
        ) {
            if (str_contains($system->updateTime, '+')) {
                return substr($system->updateTime, 0, strpos($system->updateTime, '+'));
            }

            return $system->updateTime;
        }

        // EDSM dumps
        if (property_isset($system, 'updateTime')
            && is_object($system->updateTime)
            && $system->updateTime->information
        ) {
            return $system->updateTime->information;
        }

        return now();
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
