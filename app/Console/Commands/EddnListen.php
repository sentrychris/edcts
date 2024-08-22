<?php

namespace App\Console\Commands;

use ZMQ;
use ZMQContext;
use ZMQSocketException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Services\Eddn\EddnService;

class EddnListen extends Command
{
    protected $signature = "eddn:listen";

    protected $description = "Listen to the Elite Dangerous Data Network";

    private EddnService $eddnService;
    
    public function __construct(EddnService $eddnService)
    {
        parent::__construct();
        $this->eddnService = $eddnService;
    }

    public function handle()
    {
        $numCachedSystems = count(Redis::smembers("eddn_systems_from_listener"));
        if ($numCachedSystems > 100) {
            $this->error("Cache limit reached, process existing systems first");
            return false;
        }
    
        $this->info("Starting EDDN listener...");

        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_SUB);
        $socket->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, ""); // Subscribe to all messages

        $messagesDefault = ["batch" => true, "messages" => []];
        $messagesBatch = 100;
        $messagesBatchTime = 10;

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
                            // $this->processData($data);
                            $messages["messages"][] = $data;
                        }

                        // If we have more than 500 messages or 20 seconds have passed since the last messages were received, process the batch
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
        // 
    }

    protected function processBatch(array $data)
    {
        $numCachedSystems = count(Redis::smembers("eddn_systems_from_listener"));

        if ($numCachedSystems > 100) {
            throw new \RuntimeException("Cache limit reached, process existing systems first.");
        }

        $this->line("{$numCachedSystems} systems in cache.\n");

        // Implement your logic for batch processing here
        $this->info("Processing batch of EDDN messages...");

        // Cache system names with their ID64s for later processing
        $this->eddnService->cacheSystemNamesWithId64s($data);

        $this->info("Batch processed, moving on to the next batch...");
    }
}
