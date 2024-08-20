<?php

namespace App\Models;

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
        'body_count',
        'main_star',
        'updated_at',
    ];
    
    public $timestamps = false;
    
    /**
     * Get information related to the system
     * 
     * @return HasOne
     */
    public function information(): HasOne
    {
        return $this->hasOne(SystemInformation::class);
    }

    /**
     * Accessor to cache system information
     * 
     * @return mixed
     */
    public function getInformationAttribute()
    {        
        $information = Cache::remember($this->getAttributeCacheKey('information'), 3600, function() {
            return $this->getRelationValue('information');
        });

        $this->setRelation('information', $information);

        return $information;
    }

    /**
     * Get bodies related to the system
     * 
     * @return HasMany
     */
    public function bodies(): HasMany {
        return $this->hasMany(SystemBody::class);
    }

    /**
     * Accessor to cache system bodies
     * 
     * @return mixed
     */
    public function getBodiesAttribute()
    {        
        $bodies = Cache::remember($this->getAttributeCacheKey('bodies'), 3600, function() {
            return $this->getRelationValue('bodies');
        });

        $this->setRelation('bodies', $bodies);

        return $bodies;
    }

    /**
     * Get stations related to the system
     * 
     * @return HasMany
     */
    public function stations(): HasMany {
        return $this->hasMany(SystemStation::class);
    }

    /**
     * Aceessor to cache system stations
     * 
     * @return mixed
     */
    public function getStationsAttribute()
    {        
        $stations = Cache::remember($this->getAttributeCacheKey('stations'), 3600, function() {
            return $this->getRelationValue('stations');
        });

        $this->setRelation('stations', $stations);

        return $stations;
    }

    /**
     * Get fleet carrier departures from the system
     * 
     * @return HasMany
     */
    public function departures(): HasMany
    {
        return $this->hasMany(FleetSchedule::class, 'departure_system_id');
    }

    /**
     * Get fleet carrier arrivals to the system
     * 
     * @return HasMany
     */
    public function arrivals(): HasMany
    {
        return $this->hasMany(FleetSchedule::class, 'destination_system_id');
    }
    
    /**
    * Add a query filter scope to filter systems.
    * 
    * @param Builder $builder - the query builder
    * @param array $options - the filter options
    * @param bool $exact - whether to use exact match
    * @return Builder - the query builder
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
     * configure the URL slug.
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

    /**
     * Get the cache key for system related attributes.
     * 
     * @param string $type - the attribute type
     * @return string - the cache key
     */
    private function getAttributeCacheKey(string $type) {
        return "system_{$this->id64}_{$type}";
    }
}
