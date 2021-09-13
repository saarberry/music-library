<?php

namespace App\Console\Commands;

use App\Services\DiscogsApi;
use Illuminate\Console\Command;

class DiscogsSearchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discogs:search {--a|artist=} {--t|title=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search Discogs for stuff.';

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
    public function handle(DiscogsApi $discogs)
    {
        $albums = $discogs->searchReleases($this->option('artist'), $this->option('title'));
        $albums = $albums->map(function ($album) {
            return [
                'id' => $album['id'],
                'title' => $album['title'],
                'year' => $album['year'],
            ];
        });

        $this->table(
            ["ID", "Title", "Year"],
            $albums->toArray()
        );

        return 0;
    }
}
