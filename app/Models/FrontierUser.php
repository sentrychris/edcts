<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FrontierUser extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Eager load the commander with the user.
     * 
     * @var array - the eager loaded relation
     */
    protected $with = ['commander'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commander(): HasMany
    {
        return $this->hasMany(Commander::class);
    }
}
