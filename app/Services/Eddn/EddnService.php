<?php

namespace App\Services\Eddn;

use App\Models\System;
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

                    $systemRecordExists = System::whereId64($starSystemId64)
                        ->whereName($starSystem)
                        ->exists();

                    if (! $systemRecordExists) {
                        System::create([
                            'id64' => $starSystemId64,
                            'name' => $starSystem,
                            'coords' => json_encode([
                                'x' => $message["StarPos"][0],
                                'y' => $message["StarPos"][1],
                                'z' => $message["StarPos"][2],
                            ]),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // // If the event is a scan and the system is not a duplicate in this batch of messages then process it
                // if (
                //     isset($message["StarSystem"])
                //     && isset($message["SystemAddress"])
                //     && !in_array($message["StarSystem"], $duplicateSystems)
                // ) {
                //     $starSystem = $message["StarSystem"];
                //     $starSystemId64 = $message["SystemAddress"];

                //     $cacheValue = $starSystemId64."-".str_replace(" ", "+", $starSystem);
                //     if(!in_array($cacheValue, $cachedSystemsToProcess)) { // Check if the system already exists in the cache
                //         Redis::sadd("eddn_systems_from_listener", $starSystemId64."-".str_replace(" ", "+", $starSystem));
                //     }

                //     // Add the system to the list for the next iteration in this batch
                //     $duplicateSystems[] = $starSystem;
                // }
            }
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