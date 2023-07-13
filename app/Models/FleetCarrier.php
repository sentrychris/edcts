<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasQueryFilter;

class FleetCarrier extends Model
{
    use HasFactory, HasQueryFilter;

    protected $table = 'fleet_carriers';

    protected $guarded = [];

    public function schedule(): HasMany
    {
        return $this->hasMany(FleetSchedule::class);
    }
    
    public function scopeFilter(Builder $builder, array $options)
    {
        if (!empty($options['search'])) {
            $builder->search($options['search']);
        }

        return $this->buildFilterQuery($builder, $options, [
            'name',
            'identifier'
        ]);
    }
}
