<?php

namespace App\Services\EDSM;

use Exception;
use App\Libraries\EliteAPIManager;
use App\Models\Commander;
use App\Models\System;

class CommanderService
{
    public static function importFlightLogFromEDSM(Commander $commander, string $startDateTime, string $endDateTime)
    {
        $key = $commander->edsm_api_key;
        if (! $key) {
            throw new Exception('Error! Commander does not have an associated EDSM API key.');
        }

        $api = app(EliteAPIManager::class);
        $response = $api->setConfig(config('elite.edsm'))
            ->setCategory('commander')
            ->get('flight-log', [
                'commanderName' => $commander->cmdr_name,
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

                $commander->flightLog()->updateOrCreate([
                    'system_id' => $system->id,
                    'system' => $system->name,
                    'first_discover' => $log->firstDiscover,
                    'visited_at' => $log->date
                ]);
            }
        }
    }
}