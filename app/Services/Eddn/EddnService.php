<?php

namespace App\Services\Eddn;

use App\Models\System;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class EddnService
{
    /**
     * Software
     * 
     * @var array
     */
    protected array $software = [
        'whitelist' => [],
        'blacklist' => [],
    ];

    /**
     * Valid message schemas
     * 
     * @var array
     */
    protected $schemas = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->software = config('elite.eddn.software');
        $this->schemas = config('elite.eddn.schemas');
    }

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

    /**
     * Format message attributes.
     * 
     * @param string $attribute
     * @return string
     */
    private function sanitizeMessageAttribute (string $attribute)
    {
        $value = "";

        if (str_contains($attribute, '$')) {
            if (str_starts_with($attribute, '$SYSTEM_SECURITY_')) {
                $value = str_replace(";", "", trim(str_replace('$SYSTEM_SECURITY_', '', $attribute)));
            } else {
                $parts = explode("_", $attribute);
                $value = count($parts) === 2
                    ? str_replace(";", "", $parts[1])
                    : str_replace(";", "", $attribute);
                }
        } else {
            $value = str_replace(";", "", $attribute);
        }

        if (ctype_digit($value)) {
            return (int) $value;
        } else {
            return ucfirst(trim($value));
        }
    }

    /**
     * Validate the message schema reference.
     * 
     * @param string $schemaRef
     * @return bool
     */
    public function validateSchemaRef(string $schemaRef) {
        if (! in_array($schemaRef, $this->schemas["valid"])) {
            return false;
        }

        return true;
    }
    
    /**
     * Check if the software that sent the message is allowed.
     * 
     * @param string $softwareName
     * @param string $softwareVersion
     * @param array $messageHeaders
     * @return bool
     */
    protected function isSoftwareAllowed(array $messageHeader): bool
    {
        $softwareName = $messageHeader["softwareName"];
        $softwareVersion = $messageHeader["softwareVersion"];

        // This should never happen considering messages are validated on the EDDN gateway...
        // But just in case I fuck up somewhere...
        if (!$softwareName || !$softwareVersion) {
            return false;
        }

        if (array_key_exists($softwareName, $this->software["blacklist"])) {
            return false;
        }

        if (array_key_exists($softwareName, $this->software["whitelist"])) {
            $version = $this->software["whitelist"][$softwareName];

            if ($version === "*") {
                return true;
            }

            if (version_compare($softwareVersion, $version, ">=")) {
                return true;
            }
        }

        return false;
    }
}