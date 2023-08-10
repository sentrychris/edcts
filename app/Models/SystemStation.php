<?php

namespace App\Models;

use App\Libraries\EliteAPIManager;
use App\Traits\HasQueryFilter;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
            'name'
        ], $exact);
    }

    /**
     * import from API
     * 
     * @param string $slug
     * 
     * @return System|false
     */
    public static function checkAPI(System $system)
    {
        $api = app(EliteAPIManager::class);
        $response = $api->setConfig(config('elite.edsm'))
            ->setCategory('systems')
            ->get('station', [
                'systemName' => $system->name,
                'showId' => true
            ]);

        if ($response) {
            $station = $system->stations()->updateOrCreate([
                // assign api response attributes here;
            ]);
        }

        if (! $station) {
            return false;
        }

        return $system;
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
