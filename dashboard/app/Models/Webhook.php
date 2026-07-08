<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'secret',
        'is_active',
        'events',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'events' => 'array',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }
}
