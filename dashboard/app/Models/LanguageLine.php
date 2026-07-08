<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanguageLine extends Model
{
    use HasFactory;

    protected $fillable = ['group', 'key', 'text'];

    protected $casts = [
        'text' => 'array',
    ];

    public function getTextViAttribute(): ?string
    {
        return $this->text['vi'] ?? null;
    }

    public function getTextEnAttribute(): ?string
    {
        return $this->text['en'] ?? null;
    }
}
