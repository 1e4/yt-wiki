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
    public function handle()
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

    private function getTopVideosForCountry(string $cc, string $pageToken): \stdClass
    {
        $req = Http::youtubeSearch($cc, $pageToken)
            ->get('')
            ->body();

        $req = json_decode($req);

        Cache::put("youtube.{$cc}.top_videos", $req);

        return $req;
    }

    private function getVideoInfo($cc, $videoId): void
    {
        $req = Http::youtubeVideo($videoId)
            ->get('')
            ->body();

        $req = json_decode($req);

        Cache::put("youtube.{$cc}.{$videoId}", $req);
    }

    private function getAllVideosFor(string $cc, int $maxPages = 2): array {
        $videos = [];

        for($i=0;$i<$maxPages;$i++) {
            $req = $this->getTopVideosForCountry($cc, isset($req) ? $req->nextPageToken : '');

            $videos[] = $req->items;
        }

        return Arr::flatten($videos);
    }
}
