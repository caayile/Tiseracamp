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

if (! function_exists('cv_job_board_recommendations')) {
    /**
     * Build LinkedIn / Glints / Jobstreet search links for CV review roles.
     *
     * @param  array{target_position?:?string,suggested_role?:?string,location?:?string,alternatives?:array<int,string>}  $context
     * @return array<int, array{role:string,boards:array<int, array{name:string,label:string,url:string,color:string}>}>
     */
    function cv_job_board_recommendations(array $context): array
    {
        $primary = trim((string) ($context['suggested_role'] ?? ''))
            ?: trim((string) ($context['target_position'] ?? ''));

        $roles = [];
        if ($primary !== '') {
            $roles[] = $primary;
        }

        foreach ($context['alternatives'] ?? [] as $alt) {
            $alt = trim((string) $alt);
            if ($alt !== '' && ! in_array($alt, $roles, true)) {
                $roles[] = $alt;
            }
            if (count($roles) >= 3) {
                break;
            }
        }

        if ($roles === []) {
            return [];
        }

        $location = trim((string) ($context['location'] ?? '')) ?: 'Indonesia';

        $boardsFor = function (string $role) use ($location): array {
            $q = rawurlencode($role);
            $loc = rawurlencode($location);
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $role) ?? '', '-'));

            return [
                [
                    'name' => 'linkedin',
                    'label' => 'LinkedIn',
                    'url' => "https://www.linkedin.com/jobs/search/?keywords={$q}&location={$loc}",
                    'color' => 'bg-[#0A66C2] text-white hover:bg-[#004182]',
                ],
                [
                    'name' => 'glints',
                    'label' => 'Glints',
                    'url' => "https://glints.com/id/opportunities/jobs/explore?keyword={$q}&country=ID",
                    'color' => 'bg-[#0F1B2A] text-white hover:bg-[#1a2d45]',
                ],
                [
                    'name' => 'jobstreet',
                    'label' => 'Jobstreet',
                    'url' => $slug !== ''
                        ? "https://id.jobstreet.com/{$slug}-jobs"
                        : "https://id.jobstreet.com/jobs?keywords={$q}",
                    'color' => 'bg-[#0D74CE] text-white hover:bg-[#0a5ca3]',
                ],
            ];
        };

        return array_map(fn (string $role) => [
            'role' => $role,
            'boards' => $boardsFor($role),
        ], $roles);
    }
}
