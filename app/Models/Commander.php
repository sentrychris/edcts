<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Commander extends Model
{
    use HasFactory;

    protected $table = 'commanders';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function carriers(): HasMany
    {
        return $this->hasMany(FleetCarrier::class);
    }

    public function schedule(): HasManyThrough
    {
        return $this->hasManyThrough(FleetSchedule::class, FleetCarrier::class);
    }
}
