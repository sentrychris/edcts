<?php

namespace App\Models;

use App\Traits\HasQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class System extends Model
{
    use HasFactory, HasQueryFilter;

    protected $table = 'systems';

    protected $fillable = [
        'id64',
        'name',
        'coords',
        'updated_at'
    ];

    public $timestamps = false;

    public function information(): HasOne
    {
      return $this->hasOne(SystemInformation::class);
    }

    /**
     * Filter scope
     */
    public function scopeFilter(Builder $builder, array $options, string $operand): Builder
    {
        if (!empty($options['search'])) {
            $builder->search($options['search']);
        }

        return $this->buildFilterQuery($builder, $options, [
          'name',
          'main_star'
        ], $operand);
    }
}
