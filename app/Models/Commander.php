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

    /**
     * The table associated with the model.
     * 
     * @var string - the table name
     */
    protected $table = 'commanders';

    /**
     * Guarded attributes that should not be mass assignable.
     * 
     * @var array - the guarded attributes
     */
    protected $guarded = [];

    /**
     * Get the user that owns the commander.
     * 
     * @return BelongsTo - the user that owns the commander
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get fleet carriers owned by the commander.
     * 
     * @return HasMany - the fleet carriers owned by the commander
     */
    public function carriers(): HasMany
    {
        return $this->hasMany(FleetCarrier::class);
    }

    /**
     * Get the fleet carrier journey schedules for the commander's fleet carriers.
     * 
     * @return HasManyThrough - the fleet carrier journey schedules
     */
    public function carriersJourneySchedule(): HasManyThrough
    {
        return $this->hasManyThrough(FleetCarrierJourneySchedule::class, FleetCarrier::class);
    }
}
