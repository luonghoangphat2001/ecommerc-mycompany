<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'title',
        'name',
        'menuable_type',
        'menuable_id',
        'url',
        'route',
        'route_parameters',
        'target',
        'use_menuable_name',
        'link_class',
        'wrapper_class',
        'parameters',
        'order',
    ];

    protected $casts = [
        'title' => 'array',
        'route_parameters' => 'array',
        'parameters' => 'array',
        'use_menuable_name' => 'boolean',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy(Schema::hasColumn('menu_items', 'order') ? 'order' : 'id');
    }

    public function getLabelAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        if ($this->name) {
            return $this->name;
        }

        if (is_array($this->title)) {
            return (string) ($this->title[app()->getLocale()] ?? reset($this->title) ?: '');
        }

        return (string) $this->title;
    }
}
