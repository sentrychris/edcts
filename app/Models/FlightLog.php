<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlightLog extends Model
{
    use HasFactory;

    protected $table = 'flight_log';

    protected $fillable = [
        'system_id',
        'system',
        'first_discover',
        'visited_at'
    ];

    public $timestamps = false;

    public function commander(): BelongsTo
    {
        return $this->belongsTo(Commander::class);
    }

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }
}
