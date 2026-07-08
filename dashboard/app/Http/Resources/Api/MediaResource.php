<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class MediaResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge([
            'id' => $this->id,
            'disk' => $this->disk,
            'directory' => $this->directory,
            'visibility' => $this->visibility,
            'name' => $this->name,
            'path' => $this->path,
            'url' => \Illuminate\Support\Facades\Storage::url($this->path),
            'width' => $this->width,
            'height' => $this->height,
            'size' => $this->size,
            'type' => $this->type,
            'ext' => $this->ext,
            'alt' => $this->alt ?? null,
            'title' => $this->title ?? null,
            'description' => $this->description ?? null,
            'caption' => $this->caption ?? null,
            'exif' => $this->exif ?? null,
            'curations' => $this->curations ?? null,
            'tenant_id' => $this->tenant_id ?? null,
        ], $this->getTimestamps());
    }
}
