<?php

namespace App\Console\Commands;

use App\Services\LastFmApi;
use Illuminate\Console\Command;

class LastfmYearlyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lastfm:yearly {user} {--y|year=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List yearly albums for the given user.';

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
        $year = (int) $this->option('year');

        if (!$year) {
            $year = now()->year;
        }

        dump($lastfm->yearlyAlbumsForUser($this->argument('user'), $year));

        return 0;
    }
}
