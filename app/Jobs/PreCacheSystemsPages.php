<?php

namespace App\Jobs;

use Exception;
use App\Http\Requests\SearchSystemRequest;
use App\Models\System;
use App\Traits\HasValidatedQueryRelations;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Spatie\DiscordAlerts\Facades\DiscordAlert;

class PreCacheSystemsPages implements ShouldQueue
{
    use Dispatchable, HasValidatedQueryRelations, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private string $channel;

    /**
     * @var int
     */
    private $flush = false;

    /**
     * @var int
     */
    public $timeout = 0; // no timeout

    /**
     * @var int
     */
    private int $ttl;

    /**
     * Create a new job instance.
     * 
     * @param string $channel
     * @param int $ttl
     */
    public function __construct(string $channel, bool $flush, int $ttl)
    {
        $this->channel = $channel;
        $this->flush = $flush;
        $this->ttl = $ttl;

        $this->setAllowedQueryRelations([
            'withInformation' => 'information',
            'withBodies' => 'bodies',
            'withStations' => 'stations',
            'withDepartures' => 'departures.destination',
            'withArrivals' => 'arrivals.departure'
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel($this->channel)->info("Pre-caching first 1,000 pages of systems...");

        DiscordAlert::to('pages-cache')
            ->message("Pre-caching first 1,000 pages of systems, please wait...");

        $params = [
            "withInformation" => "1",
        ];

        $pages = 1000;

        $errors = 0;
        for ($page = 1; $page <= $pages; $page++) {
            URL::forceRootUrl(config('app.url'));
            request()->server->set('REQUEST_URI', "/api/systems?page={$page}");

            $request = new SearchSystemRequest(array_merge($params, ['page' => $page]));

            if ($page > 1) {
                $request->merge(['page' => $page]);
            }

            $request->setContainer(app(Container::class))
                ->setRedirector(app('redirect'));

            $request->validateResolved();
            $validated = $request->validated();
            $limit = $request->get('limit', config('app.pagination.limit'));

            try {
                if ($this->flush) {
                    Cache::forget("systems_page_{$page}");
                }

                $systems = System::filter($validated, (int)$request->exactSearch)
                    ->paginate($limit, ['*'], 'page', $page)
                    ->appends($request->all());

                $systems = $this->loadValidatedRelationsForQuery($validated, $systems);

                Cache::set("systems_page_{$page}", $systems, $this->ttl);
            } catch (Exception $e) {
                Log::channel($this->channel)
                    ->error("Failed to pre-cache systems_page_{$page} of {$pages}: " . $e->getMessage());

                DiscordAlert::to('pages-cache')
                    ->message("Failed to pre-cache systems_page_{$page} of {$pages}: `" . $e->getMessage() . "`");

                $errors++;
            }
        }

        Log::channel($this->channel)
            ->info("Systems page pre-caching completed with " . number_format($errors) . " errors.");

        DiscordAlert::to('pages-cache')
            ->message("Systems page pre-caching completed with " . number_format($errors) . " errors.");
    }
}
