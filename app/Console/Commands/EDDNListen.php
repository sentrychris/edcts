<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use ZMQ;
use ZMQContext;
use ZMQSocketException;

class EDDNListen extends Command
{
    protected $signature = 'eddn:listen';
    protected $description = 'Listen to the Elite Dangerous Data Network';

    protected $relay = 'tcp://eddn.edcd.io:9500';
    
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting EDDN listener...');

        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_SUB);
        $socket->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, ""); // Subscribe to all messages

        $messagesDefault = ['batch' => true, 'messages' => []];
        $messagesBatch = 500;
        $messagesBatchTime = 20;

        $messages = $messagesDefault;
        $lastTimeMessages = time();

        try {
            $socket->connect($this->relay);
            $this->info('Connected to: ' . $this->relay);

            while (true) {
                try {
                    $message = $socket->recv();

                    if ($message !== false) {
                        $decompressedMessage = zlib_decode($message);

                        if ($decompressedMessage === false) {
                            $this->error('Failed to decompress message');
                            continue;
                        }

                        $data = json_decode($decompressedMessage, true);

                        if ($data) {
                            $this->processEDDNData($data);
                            $messages['messages'][] = $data;
                        }

                        if (count($messages['messages']) >= $messagesBatch || time() > ($lastTimeMessages + $messagesBatchTime)) {
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
                    $this->error('ZMQSocketException: ' . $e->getMessage());
                    break;
                }
            }
        } catch (\Exception $e) {
            $this->error('Could not connect: ' . $e->getMessage());
        }
    }

    protected function processEDDNData(array $data)
    {
        $this->info('Received EDDN data ' . json_encode($data));
        // Your logic to handle EDDN data
        // For example, save to a database, send notifications, etc.
    }

    protected function processBatch(array $messages)
    {
        // Implement your logic for batch processing here
        $this->info('Processing batch of ' . count($messages['messages']) . ' messages');
        // For example, save to the database, trigger other services, etc.
    }
}
