<?php

namespace App\Models;

use App\Services\EdsmApiService;
use App\Traits\HasQueryFilter;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class SystemStation extends Model
{
    use HasFactory, HasQueryFilter, Sluggable, SluggableScopeHelpers, SoftDeletes;
    
    protected $table = 'systems_stations';
    
    protected $guarded = [];
    
    public $timestamps = false;
    
    /**
     * System relation
     */
    public function system(): BelongsTo
    {
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
        
        return $this->buildFilterQuery($builder, $options, [
            'name',
            'type'
        ], $exact);
    }

    /**
     * Fetch body as object
     */
    protected function body(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? json_decode($value) : null
        );
    }

    /**
     * Fetch other services as array
     */
    protected function otherServices(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? explode(',', $value) : null
        );
    }

    /**
     * configure slug
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['market_id', 'name'],
                'separator' => '-'
            ]
        ];
    }
}
