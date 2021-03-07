<?php

namespace Tests\Unit;

use App\Exceptions\LastFmApiException;
use App\Exceptions\LastFmApiRateLimitException;
use App\Services\LastFmApi;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LastFmApiTest extends TestCase
{
    public function test_api_will_throw_an_exception_if_rate_limited()
    {
        Http::fake(function ($request) {
            return Http::response('{"error":29}', 403);
        });

        $api = new LastFmApi;

        $this->expectException(LastFmApiRateLimitException::class);
        $api->searchAlbums("Example");
    }

    public function test_api_will_throw_a_general_exception_if_things_go_wrong()
    {
        Http::fake(function ($request) {
            return Http::response('{"error":8}', 403);
        });

        $api = new LastFmApi;

        $this->expectException(LastFmApiException::class);
        $api->searchAlbums("Example");
    }

    public function test_albums_can_be_searched_by_query()
    {
        Http::fake(function ($request) {
            return Http::response('{"results":{"opensearch:Query":{"#text":"","role":"request","searchTerms":"Igor","startPage":"1"},"opensearch:totalResults":"83914","opensearch:startIndex":"0","opensearch:itemsPerPage":"5","albummatches":{"album":[{"name":"Igor","artist":"Tyler, the Creator","url":"https://www.last.fm/music/Tyler,+the+Creator/Igor","image":[{"#text":"https://lastfm.freetls.fastly.net/i/u/34s/09bc862022fa580b820e065e51da7905.png","size":"small"},{"#text":"https://lastfm.freetls.fastly.net/i/u/64s/09bc862022fa580b820e065e51da7905.png","size":"medium"},{"#text":"https://lastfm.freetls.fastly.net/i/u/174s/09bc862022fa580b820e065e51da7905.png","size":"large"},{"#text":"https://lastfm.freetls.fastly.net/i/u/300x300/09bc862022fa580b820e065e51da7905.png","size":"extralarge"}],"streamable":"0","mbid":""},{"name":"Ignorance is Bliss","artist":"Skepta","url":"https://www.last.fm/music/Skepta/Ignorance+is+Bliss","image":[{"#text":"https://lastfm.freetls.fastly.net/i/u/34s/c105c7d23ee48ae0519b89f634c702d4.png","size":"small"},{"#text":"https://lastfm.freetls.fastly.net/i/u/64s/c105c7d23ee48ae0519b89f634c702d4.png","size":"medium"},{"#text":"https://lastfm.freetls.fastly.net/i/u/174s/c105c7d23ee48ae0519b89f634c702d4.png","size":"large"},{"#text":"https://lastfm.freetls.fastly.net/i/u/300x300/c105c7d23ee48ae0519b89f634c702d4.png","size":"extralarge"}],"streamable":"0","mbid":""},{"name":"Ignore the Ignorant","artist":"The Cribs","url":"https://www.last.fm/music/The+Cribs/Ignore+the+Ignorant","image":[{"#text":"https://lastfm.freetls.fastly.net/i/u/34s/3723ff994d9345b3c51cb9bfd788325c.png","size":"small"},{"#text":"https://lastfm.freetls.fastly.net/i/u/64s/3723ff994d9345b3c51cb9bfd788325c.png","size":"medium"},{"#text":"https://lastfm.freetls.fastly.net/i/u/174s/3723ff994d9345b3c51cb9bfd788325c.png","size":"large"},{"#text":"https://lastfm.freetls.fastly.net/i/u/300x300/3723ff994d9345b3c51cb9bfd788325c.png","size":"extralarge"}],"streamable":"0","mbid":"4c64a464-e689-412b-b337-666302b89bb3"},{"name":"Ignorant Art","artist":"Iggy Azalea","url":"https://www.last.fm/music/Iggy+Azalea/Ignorant+Art","image":[{"#text":"https://lastfm.freetls.fastly.net/i/u/34s/1a6196c2f5104beb96498d5960d8f111.png","size":"small"},{"#text":"https://lastfm.freetls.fastly.net/i/u/64s/1a6196c2f5104beb96498d5960d8f111.png","size":"medium"},{"#text":"https://lastfm.freetls.fastly.net/i/u/174s/1a6196c2f5104beb96498d5960d8f111.png","size":"large"},{"#text":"https://lastfm.freetls.fastly.net/i/u/300x300/1a6196c2f5104beb96498d5960d8f111.png","size":"extralarge"}],"streamable":"0","mbid":"871ad9cc-7f32-4d08-a904-fc8be15668b5"},{"name":"Ignorance Never Dies","artist":"Your Demise","url":"https://www.last.fm/music/Your+Demise/Ignorance+Never+Dies","image":[{"#text":"https://lastfm.freetls.fastly.net/i/u/34s/b4a128ec6f034e6fa462fa62f0a00b82.png","size":"small"},{"#text":"https://lastfm.freetls.fastly.net/i/u/64s/b4a128ec6f034e6fa462fa62f0a00b82.png","size":"medium"},{"#text":"https://lastfm.freetls.fastly.net/i/u/174s/b4a128ec6f034e6fa462fa62f0a00b82.png","size":"large"},{"#text":"https://lastfm.freetls.fastly.net/i/u/300x300/b4a128ec6f034e6fa462fa62f0a00b82.png","size":"extralarge"}],"streamable":"0","mbid":"a59d64dc-4bf4-4d89-bd30-0162537c8e76"}]},"@attr":{"for":"Igor"}}}');
        });

        $api = new LastFmApi;
        $albums = $api->searchAlbums("Example");

        Http::assertSent(function (Request $request) {
            return $request['album'] == "Example";
        });

        $this->assertEquals($albums->count(), 5);
    }
}
