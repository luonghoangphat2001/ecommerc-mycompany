<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $guarded = [];

    protected $casts = [
        'manipulations' => 'array',
        'custom_properties' => 'array',
        'generated_conversions' => 'array',
        'responsive_images' => 'array',
        'exif' => 'array',
        'curations' => 'array',
    ];

    public function getPreviewUrlAttribute(): ?string
    {
        $path = $this->path ?: $this->file_name;

        if (! $path) {
            return null;
        }

        foreach (['http://', 'https://', '/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $path;
            }
        }

        if ($path === '') {
            return $path;
        }

        try {
            return Storage::disk($this->disk ?: 'public')->url($path);
        } catch (\Throwable) {
            return Storage::url($path);
        }
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/')
            || in_array(strtolower((string) $this->ext), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif'], true);
    }
}
