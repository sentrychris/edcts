<?php

namespace App\Models;

use App\Traits\HasQueryFilter;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class FleetCarrierJourneySchedule extends Model
{
    use HasFactory, HasQueryFilter, Sluggable, SluggableScopeHelpers, SoftDeletes;

    /**
     * The table associated with the model.
     * 
     * @var string - the table name
     */
    protected $table = 'fleet_carriers_journey_schedule';

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
     * Boot method for the model.
     * 
     * Addds a global scope to automatically order the results by departure date.
     * 
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope('order', function(Builder $builder) {
            $builder->orderBy('departs_at', 'asc');
        });
    }

    /**
     * Get the system the fleet carrier departs from.
     * 
     * @return BelongsTo - the system the fleet carrier departs from
     */
    public function departure(): BelongsTo
    {
        return $this->belongsTo(System::class, 'departure_system_id');
    }

    /**
     * Get the system the fleet carrier is heading to.
     * 
     * @return BelongsTo - the system the fleet carrier is heading to
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(System::class, 'destination_system_id');
    }

    /**
     * Get the fleet carrier responsible for the journey.
     * 
     * @return BelongsTo - the fleet carrier making the journey
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(FleetCarrier::class, 'fleet_carrier_id');
    }

    /**
     * Add a query filter scope to filter carrier journeys by departure and/or destination.
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

        if (Arr::exists($options, 'departure')) {
            $builder->whereHas('departure', function($qb) use ($options, $exact) {
                if (!$exact) {
                    $qb->where('name', 'RLIKE', $options['departure']);
                } else {
                    $qb->where('name', $options['departure']);
                }
            });
        }

        if (Arr::exists($options, 'destination')) {
            $builder->whereHas('destination', function($qb) use ($options, $exact) {
                if (!$exact) {
                    $qb->where('name', 'RLIKE', $options['destination']);
                } else {
                    $qb->where('name', $options['destination']);
                }
            });
        }

        return $builder;
    }

    /**
     * Get the number of fleet carriers leaving in the next number of days.
     * 
     * @param int $days - the number of days to check
     * @return mixed
     */
    public static function leavingInNextNumberOfDays(int $days)
    {
        return self::whereIsCancelled(0)
            ->where('departs_at', '>', now()->toDateString())
            ->where('departs_at', '<=', now()->addDays($days)->toDateTimeString())
            ->count();
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
                'source' => 'title',
                'separator' => '-'
            ]
        ];
    }
}
