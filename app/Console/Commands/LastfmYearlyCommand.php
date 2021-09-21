<?php

namespace App\Console\Commands;

use App\Services\DiscogsApi;
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
    public function handle(LastFmApi $lastfm, DiscogsApi $discogs)
    {
        $year = (int) $this->option('year');

        if (!$year) {
            $year = now()->year;
        }

        $recentTracks = $lastfm->recentTracks(
            $this->argument('user'),
            now()->year($year)->startOfYear(),
            now()->year($year)->endOfYear(),
        );

        $albums = collect();
        foreach ($recentTracks as $page => $data) {
            if ($page == 'meta') {
                $this->info("Fetching scrobbles from LastFM ({$data['totalPages']} pages)..");
                $bar = $this->output->createProgressBar($data['totalPages']);
                $bar->start();
            } else {
                foreach ($data as $scrobble) {
                    $existingAlbum = $albums->search(function ($album) use ($scrobble) {
                        $albumSignature = strtolower("{$album['artist']} {$album['title']}");
                        $scrobbleSignature = strtolower("{$scrobble['artist']} {$scrobble['title']}");
                        return (
                            ($scrobble['mbid'] !== "" && $scrobble['mbid'] == $album['mbid']) ||
                            $scrobbleSignature === $albumSignature);
                    });
                    if ($existingAlbum === false) {
                        $albums->push(array_merge($scrobble, ['plays' => 1]));
                    } else {
                        $playcount = $albums[$existingAlbum]['plays'] + 1;
                        $albums = $albums->replace([
                            $existingAlbum => array_merge($albums[$existingAlbum], ['plays' => $playcount])
                        ]);
                    }
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info("Fetched {$albums->count()} unique album entries.");
        $this->newLine();
        $this->info("Fetching release data from Discogs ({$albums->count()} albums)..");

        $bar = $this->output->createProgressBar($albums->count());
        $bar->start();
        $albums = $albums->filter(function ($album) use ($year, $discogs, $bar) {
            $bar->advance();
            $results = $discogs->searchReleases($album['artist'], $album['title']);
            $firstResultWithYear = $results->first(fn ($result) => array_key_exists('year', $result));
            return $firstResultWithYear && $firstResultWithYear['year'] == $year;
        });

        $bar->finish();
        $this->newLine();
        $this->table(
            ["Artist", "Title", "MusicBrainz ID", "Plays"],
            $albums->sortByDesc('plays')->toArray()
        );

        return 0;
    }
}
