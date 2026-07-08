<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'handle',
        'slug',
    ];

    public function items(): HasMany
    {
        $relation = $this->hasMany(MenuItem::class);

        if (Schema::hasColumn('menu_items', 'parent_id')) {
            $relation->whereNull('parent_id');
        }

        return $relation->orderBy(Schema::hasColumn('menu_items', 'order') ? 'order' : 'id');
    }
}
