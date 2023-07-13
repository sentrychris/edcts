<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FleetSchedule extends Model
{
    use HasFactory;

    protected $table = 'fleet_schedule';

    public $timestamps = false;

    public function carrier(): BelongsTo
    {
        return $this->blongsTo(FleetCarrier::class);
    }
}
