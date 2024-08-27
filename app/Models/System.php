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

class System extends Model
{
    use HasFactory, HasQueryFilter, Sluggable, SluggableScopeHelpers, SoftDeletes;
    
    /**
     * The table associated with the model.
     * 
     * @var string - the table name
     */
    protected $table = 'systems';
    
    /**
     * Guarded attributes that should not be mass assignable.
     * 
     * @var array - the guarded attributes
     */
    protected $guarded = [];
    
    /**
     * Whether or not `created_at` and updated_at should be handled automatically.
     * 
     * @var boolean - whether or not the model should be timestamped
     */
    public $timestamps = false;
    
    /**
     * Get information related to the system.
     * 
     * This will retrieve the information relation for the system which includes stuff such
     * as government, allegiance, economy, population etc.
     * 
     * @return HasOne - the information for the system
     */
    public function information(): HasOne
    {
        return $this->hasOne(SystemInformation::class);
    }

    /**
     * Get bodies related to the system.
     * 
     * This will retrieve the celestial bodies in the system.
     * 
     * @return HasMany - the bodies in the system
     */
    public function bodies(): HasMany {
        return $this->hasMany(SystemBody::class);
    }

    /**
     * Get stations related to the system.
     * 
     * This will retrieve the stations in the system.
     * 
     * @return HasMany - the stations in the system
     */
    public function stations(): HasMany {
        return $this->hasMany(SystemStation::class);
    }

    /**
     * Get fleet carrier departures from the system.
     * 
     * @return HasMany - the fleet carrier departures from the system
     */
    public function departures(): HasMany
    {
        return $this->hasMany(FleetCarrierJourneySchedule::class, 'departure_system_id');
    }

    /**
     * Get fleet carrier arrivals to the system.
     * 
     * @return HasMany - the fleet carrier arrivals to the system
     */
    public function arrivals(): HasMany
    {
        return $this->hasMany(FleetCarrierJourneySchedule::class, 'destination_system_id');
    }
    
    /**
     * Add a query filter scope to filter systems by name.
     * 
     * This scope also allows for exact search or `like` search based on the passed options.
     * 
     * @param Builder $builder - the query builder
     * @param array $options - the filter options including the search term
     * @param bool $exact - whether or not to use exact search or `like` search
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
     * Search for systems by distance.
     * 
     * @param array $coords - the coordinates to search by
     * @param int $distance - the distance to search by in light years
     * @param int $limit - the limit of systems to return
     */
    public static function findNearest(array $coords, int $distance, int $limit = 100)
    {
        $selectRaw = <<<SQL
            id,
            id64,
            name,
            coords,
            slug,
            updated_at,
            SQRT(
                POW(JSON_EXTRACT(coords, '$.x') - ?, 2) +
                POW(JSON_EXTRACT(coords, '$.y') - ?, 2) +
                POW(JSON_EXTRACT(coords, '$.z') - ?, 2)
            ) AS distance
        SQL;

        return self::selectRaw($selectRaw, [$coords['x'], $coords['y'], $coords['z']])
            ->havingRaw("distance <= ?", [$distance])
            ->orderByRaw("distance ASC")
            ->limit($limit);
    }

    /**
     * Configure the URL slug.
     * 
     * @return array - the configuration for the slug
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
