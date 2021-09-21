<?php

namespace App\Console\Commands;

use App\Models\Album;
use App\Services\FileDownloader;
use App\Services\LastFmApi;
use Illuminate\Console\Command;

class LastfmAddAlbumCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lastfm:add-album {query}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search LastFM for the given album, and add it to the database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(LastFmApi $lastfm, FileDownloader $download)
    {
        $albums = $lastfm->searchAlbums($this->argument('query'));

        $this->table(
            ["Name", "Artist", "Image URL"],
            $albums->toArray()
        );

        $key = $this->choice(
            "Which album would you like to add?",
            [
                ...$albums->map(fn ($album) => "\"{$album->title}\" by {$album->artist}")->toArray(),
                "_" => "None of the above.",
            ]
        );

        if ($key == "_") {
            $this->info("No album selected, try another query.");
            return 0;
        }

        $album = $albums->get((int) $key);
        if (
            Album::where("title", $album->title)
            ->where("artist", $album->artist)
            ->exists()
        ) {
            $this->info("The album \"{$album->title}\" is already stored in the database, try another query.");
            return 0;
        }

        $this->newLine();
        $this->info("Downloading album image..");
        $filename = $download->fromUrl($album->image);
        $album->image = $filename;
        $album->save();
        $this->info("Stored \"{$album->title}\" in database.");

        return 0;
    }
}
