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

    public static function leavingInNextNDays(int $n)
    {
        $time = now()->addDays($n)->toDateTimeString();
        $count = FleetSchedule::whereIsCancelled(0)
            ->where('departs_at', '>', now()->toDateString())
            ->where('departs_at', '<=', $time)
            ->count();
        
        return $count;
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
