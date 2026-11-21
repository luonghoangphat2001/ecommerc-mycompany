<?php

namespace App\Ecommerce\Media\Services;

use App\Ecommerce\Media\Contracts\MediaRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class MediaUrlService
{
    public function __construct(private readonly MediaRepositoryInterface $mediaRepository)
    {
    }

    /**
     * Resolve a media ID, stored path, or URL to its display URL.
     */
    public function url(mixed $value): ?string
    {
        $value = is_array($value) ? ($value[0] ?? null) : $value;

        if (! $value) {
            return null;
        }

        if (is_numeric($value)) {
            return $this->mediaRepository->findById((int) $value)?->preview_url;
        }

        if (is_string($value) && str_starts_with($value, ['http://', 'https://', '/'])) {
            return $value;
        }

        return Storage::url((string) $value);
    }
}
