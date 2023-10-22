<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use function PHPUnit\Framework\returnSelf;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Http::macro('wiki', function() {
            return Http::baseUrl('https://en.wikipedia.org/api/rest_v1/page/summary/');
        });

        Http::macro('youtubeSearch', function(string $cc, string $pageToken) {
            return Http::baseUrl('https://www.googleapis.com/youtube/v3/search')
                ->withQueryParameters([
                    'chart'=>   'mostPopular',
                    'regionCode'    =>  $cc,
                    'key'   =>  config('services.youtube.key'),
                    'maxResults'    =>  50,
                    'order' =>  'viewCount',
                    'pageToken' =>  $pageToken
                ]);
        });

        Http::macro('youtubeVideo', function(string $id) {
            return Http::baseUrl('https://youtube.googleapis.com/youtube/v3/videos')
                ->withQueryParameters([
                    'part'=>   'snippet,contentDetails,statistics',
                    'id'    =>  $id,
                    'key'   =>  config('services.youtube.key')
                ]);
        });
    }
}
