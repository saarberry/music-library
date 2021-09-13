<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class DiscogsApi
{
    protected $url = "https://api.discogs.com/";

    /**
     * User agent to make unique calls to Discogs (required by API).
     *
     * @return string
     */
    public function userAgent(): string
    {
        $name = Str::studly(config('app.name'));
        $version = config('app.version');
        $url = config('app.url');
        return "$name/$version +$url";
    }

    /**
     * Run API request.
     *
     * @param string $endpoint
     * @param array $params
     * @return Response
     */
    protected function request(string $endpoint = "", array $params = []): Response
    {
        return Http::acceptJson()
            ->withHeaders([
                "User-Agent" => $this->userAgent(),
                "Authorization" => "Discogs token=" . config('services.discogs.token')
            ])
            ->get($this->url . $endpoint, $params);
    }

    /**
     * Search for releases by artist and title.
     *
     * @param string $artist
     * @param string $title
     * @return Collection
     */
    public function searchReleases(string $artist, string $title): Collection
    {
        $response = $this->request(
            "database/search",
            [
                "artist" => $artist,
                "release_title" => $title,
            ]
        );
        $data = $response->json();
        return collect($data['results']);
    }
}
