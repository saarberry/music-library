<?php

namespace App\Services;

use App\Exceptions\LastFmApiException;
use App\Exceptions\LastFmApiRateLimitException;
use \Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class LastFmApi
{

    protected $url = "http://ws.audioscrobbler.com/2.0/";

    /**
     * Run API request.
     *
     * @param array $params
     * @throws LastFmApiRateLimitException
     * @throws LastFmApiException
     * @return Response
     */
    protected function request($params = []): Response
    {
        $response = Http::get(
            $this->url,
            array_merge($params, [
                'api_key' => config('services.lastfm.key'),
                'format' => 'json',
            ])
        );

        if ($response->failed()) {
            $data = $response->json();
            if ($data['error'] == 29) {
                throw new LastFmApiRateLimitException();
            }

            throw new LastFmApiException();
        }

        return $response;
    }

    /**
     * Search albums by query.
     *
     * @param string $query
     * @return Collection
     */
    public function searchAlbums(string $query): Collection
    {
        $response = $this->request([
            'method' => 'album.search',
            'album' => $query,
            'limit' => 5,
        ]);

        $data = $response->json();
        return collect($data['results']['albummatches']['album']);
    }
}
