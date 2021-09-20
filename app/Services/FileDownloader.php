<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileDownloader
{
    /**
     * Download a file from URL.
     *
     * @param string $url
     * @return string
     */
    public function fromUrl(string $url): string
    {
        $content = Http::get($url);

        $ext = Str::afterLast($url, ".");
        $filename = Str::random(40) . "." . $ext;

        Storage::disk('public')->put($filename, $content);

        return $filename;
    }
}
