<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Hash;
use App\Models\LoyaltyLog;
use App\Models\LoyaltyPoint;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasRoles;
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'gender',
        'phone',
        'birthday',
        'bio',
        'github_handle',
        'twitter_handle',
        'meta_data',
        'default_shipping_address_id',
        'default_billing_address_id',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date',
        'meta_data' => 'array',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Address> */
    public function addresses(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(\App\Models\Address::class, 'addressable');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Address, self> */
    public function defaultShippingAddress(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Address::class, 'default_shipping_address_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Address, self> */
    public function defaultBillingAddress(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Address::class, 'default_billing_address_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Comment> */
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Comment::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasManyThrough<\App\Models\Payment> */
    public function payments(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(\App\Models\Payment::class, \App\Models\Order::class, 'user_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasPermissionTo('access-admin-panel') || $this->hasRole('super_admin');
    }

    /** @return HasMany<Post> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /** @return HasMany<Product> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'author_id');
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (isset($model->password) && !empty($model->password) && Hash::needsRehash($model->password)) {
                $model->password = Hash::make($model->password);
            }
        });
    }

    /** @return HasMany<\App\Models\UserMeta> */
    public function meta(): HasMany
    {
        return $this->hasMany(UserMeta::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\LoyaltyPoint> */
    public function loyaltyPoint(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(LoyaltyPoint::class, 'user_id');
    }

    /** @return HasMany<\App\Models\LoyaltyLog> */
    public function loyaltyLogs(): HasMany
    {
        return $this->hasMany(LoyaltyLog::class, 'user_id');
    }
}
