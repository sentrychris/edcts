<?php

namespace App\Services\Eddn;

use Exception;
use App\Models\System;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class EddnSystemService extends EddnService
{
    /**
     * Cache system names with their ID64s.
     * 
     * @param array $data
     * @return void
     */
    public function updateSystemsData(array $data)
    {
        foreach ($data["messages"] as $receivedMessage)
        {
            // Check the software name and version
            if (! $this->isSoftwareAllowed($receivedMessage["header"])) {
                continue;
            }

            $schemaRef = $receivedMessage['$schemaRef'];

            if ($this->validateSchemaRef($schemaRef)) {
                $message = $receivedMessage["message"];

                if (isset($message["StarSystem"])
                    && isset($message["SystemAddress"])
                    && isset($message["StarPos"]) && count($message["StarPos"]) === 3
                ) {
                    $starSystem = $message["StarSystem"];
                    $starSystemId64 = $message["SystemAddress"];

                    $existingSystem = System::whereId64($starSystemId64)
                        ->whereName($starSystem)
                        ->first();

                    if (! $existingSystem) {
                        try {
                            $system = System::create([
                                'id64' => $starSystemId64,
                                'name' => $starSystem,
                                'coords' => json_encode([
                                    'x' => $message["StarPos"][0],
                                    'y' => $message["StarPos"][1],
                                    'z' => $message["StarPos"][2],
                                ]),
                                'updated_at' => now(),
                            ]);
    
                            if (!$system && !in_array($starSystemId64, Redis::smembers("eddn_systems_not_inserted"))) {
                                Redis::sadd("eddn_systems_not_inserted", $starSystemId64);   
                            } else {
                                $this->updateSystemInformationData($system, $message);
                            }
                        } catch (Exception $e) {
                            Log::channel('eddn')->error("Failed to insert system: {$starSystem} ({$starSystemId64})", [
                                'error' => $e->getMessage(),
                            ]);
                        }
                    } else {
                        $this->updateSystemInformationData($existingSystem, $message);
                    }
                }
            }
        }
    }

    /**
     * Update sytem information data.
     * 
     * @param System $system
     * @param array $message
     * @return void
     */
    public function updateSystemInformationData (System $system, array $message)
    {
        if (isset($message["Population"])
            && isset($message["SystemAllegiance"])
            && isset($message["SystemEconomy"])
            && isset($message["SystemFaction"])
            && isset($message["SystemFaction"]["Name"])
            && isset($message["SystemFaction"]["FactionState"])
            && isset($message["SystemGovernment"])
            && isset($message["SystemSecurity"])
        ) {
            try {
                $record = [
                    'population'    => $this->sanitizeMessageAttribute($message["Population"]),
                    'allegiance'    => $this->sanitizeMessageAttribute($message["SystemAllegiance"]),
                    'economy'       => $this->sanitizeMessageAttribute($message["SystemEconomy"]),
                    'faction'       => $this->sanitizeMessageAttribute($message["SystemFaction"]["Name"]),
                    'faction_state' => $this->sanitizeMessageAttribute($message["SystemFaction"]["FactionState"]),
                    'government'    => $this->sanitizeMessageAttribute($message["SystemGovernment"]),
                    'security'      => $this->sanitizeMessageAttribute($message["SystemSecurity"]),
                ];

                $information = $system->information()
                    ->updateOrCreate(['system_id' => $system->id], $record);

                if (!$information && !in_array($system->id64, Redis::smembers("eddn_system_information_not_inserted"))) {
                    Redis::sadd("eddn_system_information_not_inserted", $system->id64);   
                }
            } catch (Exception $e) {
                Log::channel('eddn')->error("Failed to insert information for: {$system->name} ({$system->id64})", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}