<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Libraries\EliteAPIManager;

class Commander extends Model
{
    use HasFactory;

    protected $table = 'commanders';

    protected $fillable = [
        'cmdr_name',
        'inara_api_key',
        'edsm_api_key'
    ];

    /**
     * User relation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Carriers relation
     */
    public function carriers(): HasMany
    {
        return $this->hasMany(FleetCarrier::class);
    }

    /**
     * Schedule relation
     */
    public function schedule(): HasManyThrough
    {
        return $this->hasManyThrough(FleetSchedule::class, FleetCarrier::class);
    }

    /**
     * CMDR flight log
     */
    public function flightLog(): HasMany
    {
        return $this->hasMany(FlightLog::class);
    }

    public function importFlightLogFromEDSM(string $startDateTime, string $endDateTime)
    {
        $key = $this->edsm_api_key;
        if (! $key) {
            throw new Exception('Error! Commander does not have an associated EDSM API key.');
        }

        $api = app(EliteAPIManager::class);
        $response = $api->setConfig(config('elite.edsm'))
            ->setCategory('commander')
            ->get('flight-log', [
                'commanderName' => $this->cmdr_name,
                'apiKey' => $key,
                'startDateTime' => $startDateTime,
                'endDateTime' => $endDateTime
            ]);
        
        if ($response->logs) {

            $logs = array_reverse($response->logs);

            foreach ($logs as $log) {

                $system = System::whereName($log->system)->first();
                if (! $system) {
                    $system = System::checkAPI($log->system);
                }

                if (! $system) {
                    throw new Exception('Error! System '. $log->system .' not found in EDSM.');
                }

                $this->flightLog()->updateOrCreate([
                    'system_id' => $system->id,
                    'system' => $system->name,
                    'first_discover' => $log->firstDiscover,
                    'visited_at' => $log->date
                ]);
            }
        }
    }
}
