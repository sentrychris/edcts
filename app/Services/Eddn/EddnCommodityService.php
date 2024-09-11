<?php

namespace App\Services\Eddn;

use App\Models\System;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

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
                        $commodities = $message['commodities'];
                        $prohibited = isset($message['prohibited']) ? $message['prohibited'] : [];

                        Redis::set("{$system->id64}_{$station}_eddn_market_data", json_encode([
                            'station' => $message['stationName'],
                            'system' => $system->name,
                            'commodities' => $commodities,
                            'prohibited' => $prohibited,
                            'last_updated' => now()->toISOString()
                        ]));
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