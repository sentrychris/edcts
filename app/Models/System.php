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
use Illuminate\Support\Facades\Cache;

class System extends Model
{
    use HasFactory, HasQueryFilter, Sluggable, SluggableScopeHelpers, SoftDeletes;
    
    protected $table = 'systems';
    
    protected $fillable = [
        'id64',
        'name',
        'coords',
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
        $information = Cache::remember($this->getCacheKey('information'), (60*60), function() {
            return $this->getRelationValue('information');
        });

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
        $bodies = Cache::remember($this->getCacheKey('bodies'), (60*60), function() {
            return $this->getRelationValue('bodies');
        });

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
        $stations = Cache::remember($this->getCacheKey('stations'), (60*60), function() {
            return $this->getRelationValue('stations');
        });

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
        $departures = Cache::remember($this->getCacheKey('departures'), (60*60), function() {
            return $this->getRelationValue('departures');
        });

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
        $arrivals = Cache::remember($this->getCacheKey('arrivals'), (60*60), function() {
            return $this->getRelationValue('arrivals');
        });

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
    public static function checkAPI(string $slug)
    {
        $api = app(EliteAPIManager::class);
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
     * Check for system information
     */
    public function checkAPIForSystemInformation()
    {
        SystemInformation::checkAPI($this);

        return $this;
    }

    /**
     * Check for system bodies
     */
    public function checkAPIForSystemBodies()
    {
        SystemBody::checkAPI($this);

        return $this;
    }

    /**
     * Check for system stations
     */
    public function checkAPIForSystemStations()
    {
        SystemStation::checkAPI($this);

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
