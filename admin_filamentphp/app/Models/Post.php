<?php

namespace App\Models;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Tags\HasTags;
use Spatie\Translatable\HasTranslations;
use App\Models\User;

/**
 *     schema="Post",
 *     title="Post",
 *     description="Post model schema",
 *     type="object",
 *     required={"id", "title", "content", "created_at"},
 * )
 */
class Post extends Model
{
    use HasTranslations;
    use HasFactory;
    use HasTags;
    use SoftDeletes;

    /**
     * @var string
     */
    public $translatable = [
        'title',
        'content',
    ];

    protected $table = 'posts';

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'content',
        'post_type',
        'image',
        'published_at',
        'seo_title',
        'seo_description',
        'is_visible',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'date',
        'is_visible' => 'boolean',
    ];

    public function featuredImage()
    {
        return $this->belongsTo(\Awcodes\Curator\Models\Media::class, 'image');
    }

    /** @return BelongsTo<User,self> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return BelongsToMany<Category> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(PostCategory::class, 'post_category', 'post_id', 'category_id')->withTimestamps();
    }

    /** @return MorphMany<Comment> */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function scopePostType($query, $type)
    {
        return $query->where('post_type', $type);
    }
}
