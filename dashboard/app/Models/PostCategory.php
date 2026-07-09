<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 *     schema="Category",
 *     title="Category",
 *     description="Content category",
 *     type="object",
 *     required={"id", "name"},
 * )
 */
class PostCategory extends Model
{
    use HasTranslations;
    use HasFactory;
    use SoftDeletes;

    /**
     * @var string
     */
    public $translatable = [
        'name',
        'description',
    ];

    protected $table = 'categories';

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'type',
        'is_visible',
        'order_column',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_visible' => 'boolean',
    ];

    /** @return HasMany<PostCategory> */
    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PostCategory::class, 'parent_id');
    }

    /** @return BelongsTo<PostCategory,self> */
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'parent_id');
    }

    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        return $depth;
    }

    public static function getTreeSortedIds(): array
    {
        $categories = self::all();
        $buildTree = function ($parentId = null) use (&$buildTree, $categories) {
            $ids = [];
            foreach ($categories->where('parent_id', $parentId)->sortBy('name') as $category) {
                $ids[] = $category->id;
                $ids = array_merge($ids, $buildTree($category->id));
            }
            return $ids;
        };
        
        return $buildTree(null);
    }

    /** @return BelongsToMany<Post> */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_category', 'category_id', 'post_id');
    }

    public function getMenuLinkAttribute(): string
    {
        // Adjust route name if needed
        return route('categories.show', $this->slug);
    }

    public function getMenuNameAttribute(): string
    {
        return $this->name;
    }

    protected static function newFactory()
    {
        return \Database\Factories\PostCategoryFactory::new();
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}
