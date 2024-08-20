<?php

namespace App\Models;

use App\Traits\HasQueryFilter;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class SystemBody extends Model
{
    use HasFactory, HasQueryFilter, Sluggable, SluggableScopeHelpers, SoftDeletes;

    protected $table = 'systems_bodies';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * System relation
     */
    public function system(): BelongsTo {
        return $this->belongsTo(System::class);
    }

    /**
    * Filter scope
    */
    public function scopeFilter(Builder $builder, array $options, bool $exact): Builder
    {
        if (!empty($options['search'])) {
            $builder->search($options['search']);
        }

        if (Arr::exists($options, 'systemn')) {
            $builder->whereHas('system', function($qb) use ($options, $exact) {
                if (!$exact) {
                    $qb->where('name', 'RLIKE', $options['system']);
                } else {
                    $qb->where('name', $options['system']);
                }
            });
        }
        
        return $this->buildFilterQuery($builder, $options, [
            'name',
            'type'
        ], $exact);
    }

    /**
     * Fetch rings as array
     */
    protected function rings(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? json_decode($value) : null
        );
    }

    /**
     * Fetch parents as array
     */
    protected function parents(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? json_decode($value) : null
        );
    }

    /**
     * configure slug
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['id64', 'name'],
                'separator' => '-'
            ]
        ];
    }
}
