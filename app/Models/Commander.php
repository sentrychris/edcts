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

    protected $fillable = [
        'cmdr_name',
        'inara_api_key',
        'edsm_api_key'
    ];

    /**
     * User relation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Carriers relation
     */
    public function carriers(): HasMany
    {
        return $this->hasMany(FleetCarrier::class);
    }

    /**
     * Schedule relation
     */
    public function schedule(): HasManyThrough
    {
        return $this->hasManyThrough(FleetSchedule::class, FleetCarrier::class);
    }
}
