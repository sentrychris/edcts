<?php

namespace App\Models;

use App\Traits\HasQueryFilter;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FleetCarrier extends Model
{
    use HasFactory, HasQueryFilter, Sluggable, SluggableScopeHelpers, SoftDeletes;

    /**
     * The table associated with the model.
     * 
     * @var string - the table name
     */
    protected $table = 'fleet_carriers';

    /**
     * Guarded attributes that should not be mass assignable.
     * 
     * @var array - the guarded attributes
     */
    protected $guarded = [];

    /**
     * Boot method for the model.
     * 
     * Adds a deleting event to remove the associated carrier journey schedule
     * when a carrier is deleted.
     * 
     * @return void
     */
    protected static function booted(): void
    {
        static::deleting(function(FleetCarrier $carrier) {
            $carrier->carrierJourneySchedule()->delete();
        });
    }

    /**
     * Get the commander this fleet carrier belongs to.
     * 
     * @return BelongsTo - the commander this fleet carrier belongs to
     */
    public function commander(): BelongsTo
    {
        return $this->belongsTo(Commander::class);
    }

    /**
     * Get the scheduled journeys for the fleet carrier.
     * 
     * @return HasMany - the scheduled journeys for the fleet carrier
     */
    public function carrierJourneySchedule(): HasMany
    {
        return $this->hasMany(FleetCarrierJourneySchedule::class);
    }
    
    /**
     * Add a query filter scope to filter fleet carriers by name and/or identifier.
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
            'name',
            'identifier'
        ], $exact);
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
                'source' => ['identifier', 'name'],
                'separator' => '-'
            ]
        ];
    }
}
