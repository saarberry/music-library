<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LastFmApi {

    protected $url = "http://ws.audioscrobbler.com/2.0/";

    protected function request($params = [])
    {
        return Http::get(
            $this->url,
            array_merge($params, [
                'api_key' => config('services.lastfm.key'),
                'format' => 'json',
            ])
        );
    }

    public function searchAlbums($query)
    {
        return $this->request([
            'method' => 'album.search',
            'album' => $query,
            'limit' => 5,
        ]);
    }

}