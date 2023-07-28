<?php

namespace App\Models;

use App\Libraries\EliteAPIManager;
use App\Traits\HasQueryFilter;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class System extends Model
{
    use HasFactory, HasQueryFilter, Sluggable, SluggableScopeHelpers, SoftDeletes;
    
    protected $table = 'systems';
    
    protected $fillable = [
        'id64',
        'name',
        'coords',
        'updated_at'
    ];
    
    public $timestamps = false;
    
    /**
     * System information relation
     */
    public function information(): HasOne
    {
        return $this->hasOne(SystemInformation::class);
    }

    /**
     * Systemn bodies relation
     */
    public function bodies(): HasMany {
        return $this->hasMany(SystemBody::class);
    }

    /**
     * System departures relation
     */
    public function departures(): HasMany
    {
        return $this->hasMany(FleetSchedule::class, 'departure_system_id');
    }

    /**
     * System arrivals relation
     */
    public function arrivals(): HasMany
    {
        return $this->hasMany(FleetSchedule::class, 'destination_system_id');
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
            'main_star'
        ], $exact);
    }

    /**
     * import from API
     */
    public static function importFromAPI(string $source, string $slug)
    {
        $api = app(EliteAPIManager::class);
        $response = $api->setConfig(config('elite.'.$source))
            ->setCategory('systems')
            ->get('system', [
                'systemName' => $slug,
                'showCoordinates' => true,
                'showInformation' => true,
                'showId' => true
            ]);

        if ($response) {
            $system = System::create([
                'id64' => $response->id64,
                'name' => $response->name,
                'coords' => json_encode($response->coords),
                'updated_at' => now()
            ]);
        }

        if ($system) {
            return $system;
        }

        return false;
    }

    /**
     * Check for system information
     */
    public function checkForSystemInformation(string $source)
    {
        $api = app(EliteAPIManager::class);
        if (!$this->information()->exists()) {
            $response = $api->setConfig(config('elite.'.$source))
                ->setCategory('systems')
                ->get('system', [
                    'systemName' => $this->name,
                    'showInformation' => true
                ]);

            if ($response->information) {
                $data = [];
                $api->convertResponse($response->information, $data);
                $this->information()->updateOrCreate($data);
            }
        }

        return $this;
    }

    /**
     * Check for system bodies
     */
    public function checkForSystemBodies()
    {
        $api = app(EliteAPIManager::class);
        if (!$this->bodies()->exists()) {
            $response = $api->setConfig(config('elite.edsm'))
                ->setCategory('system')
                ->get('bodies', [
                    'systemName' => $this->name
                ]);

            $bodies = $response->bodies;

            if ($bodies) {
                foreach($bodies as $body) {
                    $this->bodies()->updateOrCreate([
                        // TODO not this lol... see https://github.com/EDSM-NET/FrontEnd/issues/506
                        'id64' => $body->id64 ?? random_int(100000000, 999999999),
                        'name' => $body->name,
                        'discovered_by' => $body->discovery->commander,
                        'discovered_at' => $body->discovery->date,
                        'type' => $body->type,
                        'sub_type' => $body->subType
                    ]);
                }
            }
        }

        return $this;
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
