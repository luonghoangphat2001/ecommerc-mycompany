<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'MediaResource',
    title: 'Media',
    description: 'File phương tiện',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'disk', type: 'string', example: 'public'),
        new OAT\Property(property: 'directory', type: 'string', example: 'media'),
        new OAT\Property(property: 'visibility', type: 'string', example: 'public'),
        new OAT\Property(property: 'name', type: 'string', example: 'image'),
        new OAT\Property(property: 'path', type: 'string', example: 'media/image.jpg'),
        new OAT\Property(property: 'url', type: 'string', example: '/storage/media/image.jpg'),
        new OAT\Property(property: 'width', type: 'integer', nullable: true, example: 800),
        new OAT\Property(property: 'height', type: 'integer', nullable: true, example: 600),
        new OAT\Property(property: 'size', type: 'integer', example: 102400),
        new OAT\Property(property: 'type', type: 'string', example: 'image/jpeg'),
        new OAT\Property(property: 'ext', type: 'string', example: 'jpg'),
        new OAT\Property(property: 'alt', type: 'string', nullable: true),
        new OAT\Property(property: 'title', type: 'string', nullable: true),
        new OAT\Property(property: 'description', type: 'string', nullable: true),
        new OAT\Property(property: 'caption', type: 'string', nullable: true),
        new OAT\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OAT\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
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
