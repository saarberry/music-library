<?php

namespace Tests\Feature;

use App\Models\Album;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class AlbumControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_that_index_can_search_for_albums_in_the_database()
    {
        // If I have an album with the title of test..
        Album::factory()->create(['title' => 'test']);

        // And I send a request to the API to search for it..
        $response = $this->get('api/albums?query=test');

        // I expect to receive it as only result.
        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'test']);
    }

    public function test_that_index_will_search_lastfm_if_theres_no_matches_in_the_database()
    {
        Http::fake([
            'ws.audioscrobbler.com/*' => Http::response(
                '{"results":{"opensearch:Query":{"#text":"","role":"request","searchTerms":"Igor","startPage":"1"},"opensearch:totalResults":"83914","opensearch:startIndex":"0","opensearch:itemsPerPage":"5","albummatches":{"album":[{"name":"Igor","artist":"Tyler, the Creator","url":"https://www.last.fm/music/Tyler,+the+Creator/Igor","image":[{"#text":"https://lastfm.freetls.fastly.net/i/u/34s/09bc862022fa580b820e065e51da7905.png","size":"small"},{"#text":"https://lastfm.freetls.fastly.net/i/u/64s/09bc862022fa580b820e065e51da7905.png","size":"medium"},{"#text":"https://lastfm.freetls.fastly.net/i/u/174s/09bc862022fa580b820e065e51da7905.png","size":"large"},{"#text":"https://lastfm.freetls.fastly.net/i/u/300x300/09bc862022fa580b820e065e51da7905.png","size":"extralarge"}],"streamable":"0","mbid":""},{"name":"Ignorance is Bliss","artist":"Skepta","url":"https://www.last.fm/music/Skepta/Ignorance+is+Bliss","image":[{"#text":"https://lastfm.freetls.fastly.net/i/u/34s/c105c7d23ee48ae0519b89f634c702d4.png","size":"small"},{"#text":"https://lastfm.freetls.fastly.net/i/u/64s/c105c7d23ee48ae0519b89f634c702d4.png","size":"medium"},{"#text":"https://lastfm.freetls.fastly.net/i/u/174s/c105c7d23ee48ae0519b89f634c702d4.png","size":"large"},{"#text":"https://lastfm.freetls.fastly.net/i/u/300x300/c105c7d23ee48ae0519b89f634c702d4.png","size":"extralarge"}],"streamable":"0","mbid":""},{"name":"Ignore the Ignorant","artist":"The Cribs","url":"https://www.last.fm/music/The+Cribs/Ignore+the+Ignorant","image":[{"#text":"https://lastfm.freetls.fastly.net/i/u/34s/3723ff994d9345b3c51cb9bfd788325c.png","size":"small"},{"#text":"https://lastfm.freetls.fastly.net/i/u/64s/3723ff994d9345b3c51cb9bfd788325c.png","size":"medium"},{"#text":"https://lastfm.freetls.fastly.net/i/u/174s/3723ff994d9345b3c51cb9bfd788325c.png","size":"large"},{"#text":"https://lastfm.freetls.fastly.net/i/u/300x300/3723ff994d9345b3c51cb9bfd788325c.png","size":"extralarge"}],"streamable":"0","mbid":"4c64a464-e689-412b-b337-666302b89bb3"},{"name":"Ignorant Art","artist":"Iggy Azalea","url":"https://www.last.fm/music/Iggy+Azalea/Ignorant+Art","image":[{"#text":"https://lastfm.freetls.fastly.net/i/u/34s/1a6196c2f5104beb96498d5960d8f111.png","size":"small"},{"#text":"https://lastfm.freetls.fastly.net/i/u/64s/1a6196c2f5104beb96498d5960d8f111.png","size":"medium"},{"#text":"https://lastfm.freetls.fastly.net/i/u/174s/1a6196c2f5104beb96498d5960d8f111.png","size":"large"},{"#text":"https://lastfm.freetls.fastly.net/i/u/300x300/1a6196c2f5104beb96498d5960d8f111.png","size":"extralarge"}],"streamable":"0","mbid":"871ad9cc-7f32-4d08-a904-fc8be15668b5"},{"name":"Ignorance Never Dies","artist":"Your Demise","url":"https://www.last.fm/music/Your+Demise/Ignorance+Never+Dies","image":[{"#text":"https://lastfm.freetls.fastly.net/i/u/34s/b4a128ec6f034e6fa462fa62f0a00b82.png","size":"small"},{"#text":"https://lastfm.freetls.fastly.net/i/u/64s/b4a128ec6f034e6fa462fa62f0a00b82.png","size":"medium"},{"#text":"https://lastfm.freetls.fastly.net/i/u/174s/b4a128ec6f034e6fa462fa62f0a00b82.png","size":"large"},{"#text":"https://lastfm.freetls.fastly.net/i/u/300x300/b4a128ec6f034e6fa462fa62f0a00b82.png","size":"extralarge"}],"streamable":"0","mbid":"a59d64dc-4bf4-4d89-bd30-0162537c8e76"}]},"@attr":{"for":"Igor"}}}'
            ),
        ]);

        // If I have no stored albums, but I send a request to
        // the API to search for stuff anyway..
        $response = $this->get('api/albums?query=test');

        // I expect the controller to call the lastfm api at some point.
        Http::assertSent(function (Request $request) {
            return
                Str::startsWith($request->url(), 'http://ws.audioscrobbler.com') &&
                $request['method'] == 'album.search' &&
                $request['album'] == 'test';
        });

        $response
            ->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonFragment(['title' => 'Igor']);
    }
}
