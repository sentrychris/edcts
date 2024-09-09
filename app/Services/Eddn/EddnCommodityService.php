<?php

namespace App\Services\Eddn;

use App\Models\System;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EddnCommodityService extends EddnService
{
    /**
     * Cache system names with their ID64s.
     * 
     * @param array $data
     * @return void
     */
    public function updateMarketData(array $data)
    {
        foreach ($data["messages"] as $receivedMessage)
        {
            try {
                // Check the software name and version
                if (! $this->isSoftwareAllowed($receivedMessage["header"])) {
                    continue;
                }

                $schemaRef = $receivedMessage['$schemaRef'];

                if ($this->validateSchemaRef($schemaRef) && $schemaRef === 'https://eddn.edcd.io/schemas/commodity/3') {
                    $message = $receivedMessage['message'];
                    if (! isset($message['systemName'])) {
                        continue;
                    }

                    $system = System::whereName($message['systemName'])->first();
                    if ($system && isset($message['stationName']) && isset($message['commodities'])) {
                        $station = str_replace(" ", "_", $message['stationName']);
                        $key = "{$system->id64}_{$station}_station_market_data";
                        $commodities = $message['commodities'];
                        $prohibited = isset($message['prohibited']) ? $message['prohibited'] : [];

                        Cache::remember($key, 600, function() use ($message, $commodities, $prohibited) {
                            return [
                                'station' => $message['stationName'],
                                'commodities' => $commodities,
                                'prohibited' => $prohibited
                            ];
                        });
                    }
                }
            } catch (\Exception $e) {
                Log::channel('eddn')->error("Failed to insert market data", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}