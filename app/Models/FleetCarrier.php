<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasQueryFilter;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FleetCarrier extends Model
{
    use HasFactory, HasQueryFilter, SoftDeletes;

    protected $table = 'fleet_carriers';

    protected $guarded = [];

    /**
     * Boot model
     */
    protected static function booted(): void
    {
        static::deleting(function(FleetCarrier $carrier) {
            $carrier->schedule()->delete();
        });
    }

    /**
     * Commander relation
     */
    public function commander(): BelongsTo
    {
        return $this->belongsTo(Commander::class);
    }

    /**
     * Schedule relation
     */
    public function schedule(): HasMany
    {
        return $this->hasMany(FleetSchedule::class);
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
            'name',
            'identifier'
        ], $operand);
    }
}
