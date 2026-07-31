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
