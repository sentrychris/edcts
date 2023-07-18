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

class FleetSchedule extends Model
{
    use HasFactory, HasQueryFilter, Sluggable, SluggableScopeHelpers, SoftDeletes;

    protected $table = 'fleet_schedule';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * Boot model
     */
    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope('order', function(Builder $builder) {
            $builder->orderBy('departs_at', 'asc');
        });
    }

    /**
     * Departure system relation
     */
    public function departure(): BelongsTo
    {
        return $this->belongsTo(System::class, 'departure_system_id');
    }

    /**
     * Destination system relation
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(System::class, 'destination_system_id');
    }

    /**
     * Carrier relation
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(FleetCarrier::class, 'fleet_carrier_id');
    }

    /**
     * Filter scope
     */
    public function scopeFilter(Builder $builder, array $options, string $operand): Builder
    {
        if (!empty($options['search'])) {
            $builder->search($options['search']);
        }

        return $this->buildFilterQuery($builder, $options, [
            'departure',
            'destination',
            'departs_at'
        ], $operand);
    }

    /**
     * Configure slug
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
