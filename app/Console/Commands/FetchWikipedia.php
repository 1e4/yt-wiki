<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FetchWikipedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-wikipedia {cc?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grab results from wikipedia';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cc = $this->argument('cc');

        if (!$cc) {

            foreach (config('country_codes') as $cc => $country) {
                $this->performCache($cc, $country);
            }
        }
    }

    private function performCache(string $cc, string $countryName): void
    {
        $res = Http::wiki()->get(Str::snake($countryName));

        Cache::put("wikipedia.{$cc}", json_decode($res->body()));

        $this->info("Got page from wikipedia about {$countryName}");
    }
}
