<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FleetCarrier extends Model
{
    use HasFactory;

    public function schedule(): HasMany
    {
        return $this->hasMany(FleetSchedule::class);
    }
}
