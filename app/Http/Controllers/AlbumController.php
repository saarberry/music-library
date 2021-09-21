<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlbumResource;
use App\Models\Album;
use App\Services\LastFmApi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AlbumController extends Controller
{
    /**
     * Index all resources.
     *
     * @return ResourceCollection
     */
    public function index(): ResourceCollection
    {
        return AlbumResource::collection(Album::orderBy('artist', 'asc')->get());
    }

    /**
     * Search for matches of the resource.
     *
     * @param Request $request
     * @param LastFmApi $lastfm
     * @return ResourceCollection
     */
    public function search(Request $request, LastFmApi $lastfm): ResourceCollection
    {
        $query = $request->input('query');
        $albums = Album::where('title', 'like', "%{$query}%")->get();

        if ($albums->isEmpty()) {
            $albums = $lastfm->searchAlbums($query);
        }

        return AlbumResource::collection($albums);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function show(Album $album)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Album $album)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function destroy(Album $album)
    {
        //
    }
}
