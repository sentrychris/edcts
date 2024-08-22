<?php

namespace App\Console\Commands;

use ZMQ;
use ZMQContext;
use ZMQSocketException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class EddnListen extends Command
{
    protected $signature = "eddn:listen";

    protected $description = "Listen to the Elite Dangerous Data Network";
    
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("Starting EDDN listener...");

        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_SUB);
        $socket->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, ""); // Subscribe to all messages

        $messagesDefault = ["batch" => true, "messages" => []];
        $messagesBatch = 100;
        $messagesBatchTime = 20;

        $messages = $messagesDefault;
        $lastTimeMessages = time();

        try {
            $relay = config("elite.eddn.relay.listener");
            $socket->connect($relay);
            $this->info("Connected to: {$relay}");
            $this->line("Messages batch size: {$messagesBatch}");

            while (true) {
                try {
                    $message = $socket->recv();

                    if ($message !== false) {
                        $decompressedMessage = zlib_decode($message);

                        if ($decompressedMessage === false) {
                            $this->error("Failed to decompress message");
                            continue;
                        }

                        $data = json_decode($decompressedMessage, true);

                        if ($data) {
                            $this->processData($data);
                            $messages["messages"][] = $data;
                        }

                        if (count($messages["messages"]) >= $messagesBatch || time() > ($lastTimeMessages + $messagesBatchTime)) {
                            // Process the batch of messages
                            $this->processBatch($messages);

                            // Reset messages and timer
                            $messages = $messagesDefault;
                            $lastTimeMessages = time();
                        }
                    } else {
                        // If no message received, sleep a bit to avoid tight loop
                        usleep(10000); // 10 ms
                    }
                } catch (ZMQSocketException $e) {
                    $this->error("ZMQSocketException: " . $e->getMessage());
                    break;
                }
            }
        } catch (\Exception $e) {
            $this->error("Could not connect: " . $e->getMessage());
        }
    }

    protected function processData(array $data)
    {
        // dd($data);
        // $this->info("Received EDDN data " . json_encode($data));
        // Your logic to handle EDDN data
        // For example, save to a database, send notifications, etc.
    }

    protected function processBatch(array $data)
    {
        $cachedSystemsToProcess = Redis::smembers("eddn_system_scans");

        if (count($cachedSystemsToProcess) > 100) {
            throw new \RuntimeException("Cache limit reached, process existing systems first");
        }

        // Implement your logic for batch processing here
        $this->info("Processing batch of " . count($data["messages"]) . " messages");

        $duplicateSystems = [];
        foreach ($data["messages"] as $receivedMessage)
        {
            $softwareName = $receivedMessage["header"]["softwareName"];
            $softwareVersion = $receivedMessage["header"]["softwareVersion"];
            if (! $this->isSoftwareAllowed($softwareName, $softwareVersion)) {
                continue;
            }

            $schemaRef = $receivedMessage['$schemaRef'];
            if (! in_array($schemaRef, config("elite.eddn.schemas.valid"))) {
                continue;
            }

            if ($schemaRef === "https://eddn.edcd.io/schemas/journal/1") {
                $message = $receivedMessage["message"];
                $event = $message["event"];

                if ($event === "Scan" && !in_array($message["StarSystem"], $duplicateSystems)) {
                    $starSystem = $message["StarSystem"];
                    $starSystemId64 = $message["SystemAddress"];

                    $this->line("System scan journal event received for {$starSystem}");
                    Redis::sadd("eddn_system_scans", $starSystemId64."-".str_replace(" ", "+", $starSystem));

                    $duplicateSystems[] = $starSystem;
                }
            }
        }

        $this->info("Batch processed, moving on to the next batch...");
    }

    /**
     * Check if the software that sent the message is allowed.
     * 
     * @param string $softwareName
     * @param string $softwareVersion
     * @return bool
     */
    protected function isSoftwareAllowed(string $softwareName, string $softwareVersion): bool
    {
        $software = config("elite.eddn.software");

        if (array_key_exists($softwareName, $software["blacklist"])) {
            return false;
        }

        if (array_key_exists($softwareName, $software["whitelist"])) {
            $version = $software["whitelist"][$softwareName];

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
