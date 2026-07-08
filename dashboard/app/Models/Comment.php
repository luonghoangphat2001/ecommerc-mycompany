<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';

    protected $guarded = [];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function getCommentableLabelAttribute(): string
    {
        $target = $this->relationLoaded('commentable') ? $this->commentable : null;

        if ($target) {
            foreach (['name', 'title', 'label', 'slug', 'number'] as $attribute) {
                if ($target->getAttribute($attribute)) {
                    return class_basename($target) . ' · ' . $target->getAttribute($attribute);
                }
            }
        }

        $type = $this->commentable_type ? class_basename($this->commentable_type) : 'Target';

        return $type . ' #' . ($this->commentable_id ?? '-');
    }

    /** @return BelongsTo<User,self> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /** @return MorphTo<Model,self> */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
