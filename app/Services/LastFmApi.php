<?php

namespace App\Services;

use App\Exceptions\LastFmApiException;
use App\Exceptions\LastFmApiRateLimitException;
use App\Models\Album;
use Exception;
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

    public function yearlyAlbumsForUser(string $user, int $year): Collection
    {
        $from = Carbon::now()->year($year)->startOfYear()->timestamp;
        $to = Carbon::now()->year($year)->endOfYear()->timestamp;

        // $response = $this->request([
        //     'method' => 'album.getinfo',
        //     'user' => $user,
        //     'artist' => 'Turnstile',
        //     'album' => 'Glow On',
        // ]);
        // $result = $response->json();
        // $response = Http::get($result['album']['url']);
        // preg_match(
        //     '/\<dd class\=\"catalogue-metadata-description\"\>(\d{1,2} [a-zA-Z]+ \d{4})\<\/dd\>/',
        //     $response->body(),
        //     $matches
        // );
        // dump(Carbon::parse($matches[1])->year);
        // return collect();

        $payload = [
            'method' => 'user.getrecenttracks',
            'user' => $user,
            'limit' => 200,
            'from' => $from,
            'to' => $to,
        ];
        $initialResponse = $this->request($payload);
        $initialResult = $initialResponse->json();

        $pages = (int) $initialResult['recenttracks']['@attr']['totalPages'];
        $page = (int) $initialResult['recenttracks']['@attr']['page'];

        $albums = collect();
        while ($page <= $pages) {
            $response = $this->request(
                array_merge($payload, ['page' => $page])
            );
            $result = $response->json();

            foreach ($result['recenttracks']['track'] as $track) {
                $artist = $track['artist']['#text'];
                $album = $track['album']['#text'];
                $mbid = $track['album']['mbid'];
                if (
                    !$albums->contains('mbid', $mbid) ||
                    !$albums->contains(function ($value, $key) use ($artist, $album) {
                        return "{$value['artist']} {$value['album']}" === "$artist $album";
                    })
                ) {
                    $albums->push([
                        'artist' => $track['artist']['#text'],
                        'album' => $track['album']['#text'],
                        'mbid' => $track['album']['mbid'],
                    ]);
                }
            }

            dump("Fetching scrobbles - page {$page} of {$pages}.");
            $page++;
        }

        $albums = $albums
            ->filter(fn ($album) => $album['mbid'] || ($album['artist'] && $album['album']))
            ->map(function ($album) use ($user, $year) {
                $payload = [
                    'method' => 'album.getinfo',
                    'user' => $user,
                ];
                if ($album['mbid']) {
                    $payload['mbid'] = $album['mbid'];
                } else {
                    $payload['artist'] = $album['artist'];
                    $payload['album'] = $album['album'];
                }

                dump("Fetching {$album['artist']} - {$album['album']}");
                $response = $this->request($payload);
                $result = $response->json();

                try {
                    $response = Http::get($result['album']['url']);
                    preg_match(
                        '/\<dd class\=\"catalogue-metadata-description\"\>(.*\d{4})\<\/dd\>/',
                        $response->body(),
                        $matches
                    );
                    $releaseDate = $matches[1];
                } catch (Exception $e) {
                    return null;
                }

                if (
                    Carbon::parse($releaseDate)->year !== $year ||
                    (strlen($releaseDate) == 4 && (int) $releaseDate !== $year)
                ) {
                    return null;
                }

                return [
                    'artist' => $result['album']['artist'],
                    'album' => $result['album']['name'],
                    'userplaycount' => $result['album']['userplaycount'],
                    'releasedate' => $releaseDate,
                ];
            })
            ->filter(fn ($album) => !!$album)
            ->sortByDesc('userplaycount');

        return $albums;
    }
}
