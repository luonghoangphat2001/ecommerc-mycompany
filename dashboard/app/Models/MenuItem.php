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
        'type',
        'label',
        'title',
        'name',
        'model_type',
        'model_id',
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

    protected static function booted(): void
    {
        static::creating(function (self $item) {
            if (Schema::hasColumn('menu_items', 'type') && empty($item->type)) {
                $item->type = 'custom';
            }
            if (Schema::hasColumn('menu_items', 'model_type') && $item->model_type === null) {
                $item->model_type = '';
            }
            if (Schema::hasColumn('menu_items', 'model_id') && $item->model_id === null) {
                $item->model_id = 0;
            }
            if (Schema::hasColumn('menu_items', 'target') && empty($item->target)) {
                $item->target = '_self';
            }
            if (Schema::hasColumn('menu_items', 'order') && $item->order === null) {
                $item->order = 0;
            }
        });
    }

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
        $orderColumn = Schema::hasColumn('menu_items', 'order_column')
            ? 'order_column'
            : (Schema::hasColumn('menu_items', 'order') ? 'order' : 'id');

        return $this->hasMany(self::class, 'parent_id')->orderBy($orderColumn);
    }

    public function getLabelAttribute($value): string
    {
        if (is_array($this->title) && !empty($this->title)) {
            $locale = app()->getLocale() ?: 'vi';
            return (string) ($this->title[$locale] ?? $this->title['vi'] ?? $this->title['en'] ?? reset($this->title) ?: '');
        }

        if (is_string($this->title) && str_starts_with(trim($this->title), '{')) {
            $decoded = json_decode($this->title, true);
            if (is_array($decoded) && !empty($decoded)) {
                $locale = app()->getLocale() ?: 'vi';
                return (string) ($decoded[$locale] ?? $decoded['vi'] ?? $decoded['en'] ?? reset($decoded) ?: '');
            }
        }

        if ($value) {
            return (string) $value;
        }

        if ($this->name) {
            return (string) $this->name;
        }

        return (string) $this->title;
    }
}
