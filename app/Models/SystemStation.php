<?php

namespace App\Models;

use App\Libraries\EliteAPIManager;
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
            ->setCategory('system')
            ->get(key: 'stations', subkey: 'stations', params:[
                'systemName' => $system->name,
                'showId' => true
            ]);

        $stations = $response->stations;

        if ($stations) {
            foreach ($stations as $station) {
                try {
                    $station = $system->stations()->updateOrCreate(
                        [   // composite unique key 
                            'name' => $station->name,
                            'type' => $station->type,
                        ],
                        [
                            'market_id' => $station->marketId,
                            'distance_to_arrival' => $station->distanceToArrival,
                            'body' => property_exists($station, 'body') ? json_encode($station->body) : null,
                            'allegiance' => $station->allegiance,
                            'government' => $station->government,
                            'economy' => $station->economy,
                            'second_economy' => $station->secondEconomy,
                            'has_market' => $station->haveMarket,
                            'has_shipyard' => $station->haveShipyard,
                            'has_outfitting' => $station->haveOutfitting,
                            'other_services' => is_array($station->otherServices) ? implode(',', $station->otherServices) : null,
                            'controlling_faction' => $station->controllingFaction->name,
                            'information_last_updated' => $station->updateTime->information,
                            'market_last_updated' => $station->updateTime->market,
                            'shipyard_last_updated' => $station->updateTime->shipyard,
                            'outfitting_last_updated' => $station->updateTime->outfitting,
                        ]
                    );
                } catch (Exception $e) {
                    Log::channel('system')->error($e->getMessage());
                }
            }
        }

        return $system;
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
