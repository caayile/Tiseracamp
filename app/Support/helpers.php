<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('media_disk')) {
    /**
     * Disk used for user uploads (local public or S3/R2).
     */
    function media_disk(): string
    {
        return (string) config('filesystems.upload', 'public');
    }
}

if (! function_exists('media_url')) {
    /**
     * Public URL for an uploaded file path (or absolute URL).
     */
    function media_url(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk(media_disk())->url($path);
    }
}

if (! function_exists('youtube_embed_url')) {
    /**
     * Convert YouTube watch/share/shorts URLs to an embeddable /embed/{id} URL.
     * Non-YouTube URLs are returned unchanged.
     */
    function youtube_embed_url(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/embed/|youtube-nocookie\.com/embed/)([A-Za-z0-9_-]{6,})~i', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('~(?:youtube\.com/watch\?(?:.*&)?v=|youtu\.be/|youtube\.com/shorts/|youtube\.com/live/)([A-Za-z0-9_-]{6,})~i', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        return $url;
    }
}
