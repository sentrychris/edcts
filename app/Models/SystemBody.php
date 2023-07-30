<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemBody extends Model
{
    use HasFactory;

    protected $table = 'systems_bodies';

    protected $fillable = [
        'id64',
        'body_id',
        'name',
        'discovered_by',
        'discovered_at',
        'type',
        'sub_type',
        'is_landable',
        'surface_temp',
        'radius',
        'gravity',
        'earth_masses',
        'atmosphere_type',
        'volcanism_type',
        'terraforming_state',
        'orbital_period',
        'orbital_eccentricity',
        'orbital_inclination',
        'arg_of_periapsis',
        'rotational_period',
        'is_tidally_locked',
        'semi_major_axis',
        'axial_tilt',
        'rings',
        'parents'
    ];

    public $timestamps = false;

    /**
     * System relation
     */
    public function system(): BelongsTo {
        return $this->belongsTo(System::class);
    }
}
