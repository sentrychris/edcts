<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasQueryFilter;

class FleetSchedule extends Model
{
    use HasFactory, HasQueryFilter;

    protected $table = 'fleet_schedule';

    protected $guarded = [];

    public $timestamps = false;

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(FleetCarrier::class, 'fleet_carrier_id');
    }

    public function scopeFilter(Builder $builder, array $options)
    {
        if (!empty($options['search'])) {
            $builder->search($options['search']);
        }

        return $this->buildFilterQuery($builder, $options, [
            'departure',
            'destination',
            'departs_at'
        ]);
    }
}
