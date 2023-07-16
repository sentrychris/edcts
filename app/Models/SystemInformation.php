<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemInformation extends Model
{
    use HasFactory;

    protected $table = 'systems_information';

    public function system(): BelongsTo
    {
      return $this->belongsTo(System::class);
    }
}
