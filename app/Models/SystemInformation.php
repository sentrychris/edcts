<?php

namespace App\Models;

use App\Libraries\EliteAPIManager;
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

    public static function checkAPI(System $system)
    {
        $api = app(EliteAPIManager::class);

        if (!$system->information()->exists()) {
            $response = $api->setConfig(config('elite.edsm'))
                ->setCategory('systems')
                ->get(key: 'system', params: [
                    'systemName' => $system->name,
                    'showInformation' => true
                ]);

            if ($response->information) {
                $data = [];
                $api->convertResponse($response->information, $data);

                $fillable = (new self)->fillable;
                $data = array_filter($data, function($v, $k) use ($fillable) {
                    return in_array($k, $fillable);
                }, ARRAY_FILTER_USE_BOTH);

                $system->information()->updateOrCreate($data);
            }
        }

        return $system;
    }
}
