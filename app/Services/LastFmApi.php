<?php

namespace App\Services;

use App\Exceptions\LastFmApiException;
use App\Exceptions\LastFmApiRateLimitException;
use App\Models\Album;
use Exception;
use Generator;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
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
        return collect($data['results']['albummatches']['album'])
            ->map(function ($album) {
                $result = new Album();
                $result->title = $album['name'];
                $result->artist = $album['artist'];
                $result->image = $album['image'][count($album['image']) - 1]['#text'];
                return $result;
            });
    }

    /**
     * Retrieve recent tracks for the given user.
     *
     * @param string $user
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @return Generator
     */
    public function recentTracks(string $user, ?Carbon $from = null, ?Carbon $to = null): Generator
    {
        if (!$from) $from = now()->startOfDay();
        if (!$to) $to = now()->endOfDay();

        $payload = [
            'method' => 'user.getrecenttracks',
            'user' => $user,
            'limit' => 200,
            'from' => $from,
            'to' => $to,
        ];

        $response = $this->request($payload);
        $result = $response->json();

        // Yield the meta info for an overview of how long this will take.
        $meta = collect([
            "page" => (int) $result['recenttracks']['@attr']['page'],
            "perPage" => (int) $result['recenttracks']['@attr']['perPage'],
            "user" => $result['recenttracks']['@attr']['user'],
            "total" => (int) $result['recenttracks']['@attr']['total'],
            "totalPages" => (int) $result['recenttracks']['@attr']['totalPages'],
        ]);
        yield "meta" => $meta;

        $pages = (int) $result['recenttracks']['@attr']['totalPages'];
        $page = (int) $result['recenttracks']['@attr']['page'];

        while ($page <= $pages) {
            $response = $this->request(array_merge($payload, ['page' => $page]));
            $result = $response->json();

            yield $page => collect(array_map(function ($track) {
                return [
                    'artist' => $track['artist']['#text'],
                    'title' => $track['album']['#text'],
                    'mbid' => $track['album']['mbid'],
                ];
            }, $response['recenttracks']['track']));
            $page++;
        }
    }
}
