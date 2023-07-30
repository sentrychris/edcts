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

    protected $table = 'fleet_carriers';

    protected $fillable = [
        'name',
        'identifier',
        'has_refuel',
        'has_repair',
        'has_armory',
        'has_shipyard',
        'has_outfitting',
        'has_cartographics'        
    ];

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
     * Configure slug
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
