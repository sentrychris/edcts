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
        'name',
        'type',
        'sub_type',
        'discovered_by',
        'discovered_at',
    ];

    public $timestamps = false;

    /**
     * System relation
     */
    public function system(): BelongsTo {
        return $this->belongsTo(System::class);
    }
}
