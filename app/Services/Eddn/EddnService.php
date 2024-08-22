<?php

namespace App\Services\Eddn;

abstract class EddnService
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
     * Format message attributes.
     * 
     * @param string $attribute
     * @return string
     */
    protected function sanitizeMessageAttribute (string $attribute)
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
    protected function validateSchemaRef(string $schemaRef) {
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