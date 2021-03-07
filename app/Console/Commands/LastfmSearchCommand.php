<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\LastFmApi;

class LastfmSearchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lastfm:search {query}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search LastFM for stuff.';

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
    public function handle(LastFmApi $lastfm)
    {
        $albums = $lastfm->searchAlbums($this->argument('query'));
        dump($albums);
    }
}
