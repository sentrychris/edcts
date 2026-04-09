<?php

namespace App\Jobs;

use Exception;
use App\Http\Requests\SearchSystemRequest;
use App\Models\System;
use App\Traits\HasQueryRelations;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class PreCacheSystemsPages implements ShouldQueue
{
    use Dispatchable, HasQueryRelations, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private string $channel;

    /**
     * @var bool
     */
    private bool $flush;

    /**
     * @var int
     */
    private int $pages;

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
     * @param bool $flush
     * @param int $ttl
     * @param int $pages
     */
    public function __construct(string $channel, bool $flush, int $pages, int $ttl)
    {
        $this->channel = $channel;
        $this->flush = $flush;
        $this->pages = $pages;
        $this->ttl = $ttl;

        $this->setAllowedQueryRelations([
            'withInformation' => 'information',
            'withBodies' => 'bodies',
            'withStations' => 'stations'
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel($this->channel)->info("Pre-caching first {$this->pages} pages of systems...");

        $params = [
            "withInformation" => "1",
        ];

        $errors = 0;
        for ($page = 1; $page <= $this->pages; $page++) {
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
            $limit = $request->input('limit', config('app.pagination.limit'));

            try {
                if ($this->flush) {
                    Cache::forget("systems_page_{$page}");
                }

                $systems = System::filter($validated, (int)$request->exactSearch)
                    ->simplePaginate($limit, ['*'], 'page', $page)
                    ->appends($request->all());

                $systems = $this->loadQueryRelations($validated, $systems);

                Cache::set("systems_page_{$page}", $systems, $this->ttl);
            } catch (Exception $e) {
                Log::channel($this->channel)
                    ->error("Failed to pre-cache systems_page_{$page} of {$this->pages}: " . $e->getMessage());

                $errors++;
            }
        }

        Log::channel($this->channel)
            ->info("Systems page pre-caching completed with " . number_format($errors) . " errors.");
    }
}
