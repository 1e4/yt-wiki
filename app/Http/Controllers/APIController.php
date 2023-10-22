<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class APIController extends Controller
{
    public function __invoke(string $countryCode): JsonResponse
    {
        $page = $this->composeResponse($countryCode);

        return response()
            ->json($page);

    }

    /**
     * Compose a response to return on the API
     *
     * @param string $cc
     * @return \stdClass
     */
    private function composeResponse(string $cc): \stdClass
    {
        // In an official API you'd use OpenAPI as a spec, but for this purpose we just contract our standard class
        $page = new \stdClass();
        $page->country = $cc;

        $wikipediaPage = Cache::get("wikipedia.{$cc}");

        $topVideos = Cache::get("youtube.{$cc}.top_videos");

        $limit = request()->query('limit', 5);

        // Asks for offset and page, but aren't they the same? A page is just a set offset
        // So for this purpose we treat each page as 5 results
        $offset = request()->query('offset', 0);
        $queryPage = request()->query('page', 1);

        if ($queryPage > 1) {
            $offset = $queryPage * 5;
        }

        // Compose the requested fields
        // Youtube API - Description, thumbnails standard (or default if standard doesn't exist) and high res
        // Wikipedia API - Excerpt that appears before the main sections on wikipedia
        foreach ($topVideos->items as $video) {
            $vid = Cache::get("youtube.{$cc}.{$video->id->videoId}")->items[0];

            $page->videos[] = [
                'description' => $vid->snippet->description,
                'thumbnails' => [
                    'standard' => $vid->snippet->thumbnails->standard->url ?? $vid->snippet->thumbnails->default->url,
                    'high' => $vid->snippet->thumbnails->high->url,
                ]
            ];
        }

        // Paginate - could use length aware paginator but this is simple enough
        $page->videos = array_slice($page->videos, $offset, $limit);

        // Add the excerpt
        $page->excerpt = $wikipediaPage->extract;

        return $page;
    }
}
