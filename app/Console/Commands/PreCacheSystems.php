<?php

namespace App\Console\Commands;

use App\Http\Requests\SearchSystemRequest;
use App\Models\System;
use App\Traits\HasValidatedRelations;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

class PreCacheSystems extends Command
{
    use HasValidatedRelations;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "edcts:precache:systems
        {--ttl=3600 : Time to live (default: 3600).}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Pre-cache system pages for the frontend";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Pre-caching system pages for the frontend...");

        $ttl = (int)$this->option('ttl');

        $params = [
            "withInformation" => "1",
        ];

        $this->line("\nQuery parameters: " . json_encode($params, 128));
        $this->info("\nCounting number of pages to cache, please wait...");

        $count = System::filter($params, 0)->count();
        $limit = config("app.pagination.limit");
        $pages = ceil($count / $limit);

        $this->line("Systems: " . number_format($count));
        $this->line("Number per page: " . number_format($limit));
        $this->line("Number of pages: " . number_format($pages));

        $this->info("\nPre-caching pages, please wait...");
        $bar = $this->output->createProgressBar($pages);

        for ($page = 1; $page <= $pages; $page++) {
            // Manually set the URL context
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

            $query = $request->only('name', 'exactSearch');

            $systems = System::filter($validated, (int)$request->exactSearch)
                ->paginate($request->get('limit', config('app.pagination.limit')))
                ->appends($request->all());

            $systems = $this->loadValidatedRelationsForSystem($validated, $systems);

            Cache::set("systems_page_{$page}", $systems, $ttl);

            $bar->advance();
        }

        Cache::set('systems_search_query', $query, $ttl);

        $bar->finish();

        $this->info("\nPre-caching is complete!");
    }
}
