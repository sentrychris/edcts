<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemInformation extends Model
{
    use HasFactory;

    protected $table = 'systems_information';

    protected $fillable = [
      'allegiance',
      'government',
      'faction',
      'faction_state',
      'population',
      'security',
      'economy'
    ];

    public function system(): BelongsTo
    {
      return $this->belongsTo(System::class);
    }
}
