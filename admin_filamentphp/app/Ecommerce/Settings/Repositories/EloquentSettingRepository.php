<?php

namespace App\Ecommerce\Settings\Repositories;

use App\Ecommerce\Settings\Contracts\SettingRepositoryInterface;
use App\Models\Setting;
use App\Ecommerce\Core\Repositories\BaseRepository;
use Illuminate\Support\Facades\Cache;

class EloquentSettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    /**
     * EloquentSettingRepository constructor.
     *
     * @param Setting $model
     */
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = 'ecommerce_setting_' . $key;

        // Cache-Aside Pattern
        return Cache::rememberForever($cacheKey, function () use ($key, $default) {
            $setting = $this->model->where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value): bool
    {
        $cacheKey = 'ecommerce_setting_' . $key;
        Cache::forget($cacheKey);

        return (bool) $this->model->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(string $feature): bool
    {
        $value = $this->get($feature, false);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}

