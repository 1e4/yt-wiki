<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchYoutube extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-youtube {cc?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the most popular youtube videos';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $cc = $this->argument('cc');

        if(!isset($cc)) {
            foreach (config('country_codes') as $cc => $value) {
                $this->performCache($cc);
            }
        } else {
            $this->performCache($cc);
        }
    }

    /**
     * Grab all the videos, then the information for each video
     *
     * @param string $cc
     * @return void
     */
    private function performCache(string $cc): void {
        $videos = $this->getAllVideosFor($cc);

        $count = 0;
        foreach($videos as $video) {
            $count++;
            if(isset($video->id->videoId)) {
                $this->getVideoInfo($cc, $id = $video->id->videoId);
                $this->info("{$count} Got info for video id: $id");
            }
        }
    }

    /**
     * Get the top videos for a specific country based off country code
     *
     * @param string $cc
     * @param string $pageToken
     * @return \stdClass
     */
    private function getTopVideosForCountry(string $cc, string $pageToken): \stdClass
    {
        $req = Http::youtubeSearch($cc, $pageToken)
            ->get('')
            ->body();

        $req = json_decode($req);

        Cache::put("youtube.{$cc}.top_videos", $req);

        return $req;
    }

    /**
     * Get information for a specific video based on video ID
     * CC is needed for caching technique
     *
     * @param $cc
     * @param $videoId
     * @return void
     */
    private function getVideoInfo($cc, $videoId): void
    {
        $req = Http::youtubeVideo($videoId)
            ->get('')
            ->body();

        $req = json_decode($req);

        Cache::put("youtube.{$cc}.{$videoId}", $req);
    }

    /**
     * Get all videos for a country
     *
     * @param string $cc
     * @param int $maxPages
     * @return array
     */
    private function getAllVideosFor(string $cc, int $maxPages = 2): array {
        $videos = [];

        for($i=0;$i<$maxPages;$i++) {
            $req = $this->getTopVideosForCountry($cc, isset($req) ? $req->nextPageToken : '');

            $videos[] = $req->items;
        }

        return Arr::flatten($videos);
    }
}
