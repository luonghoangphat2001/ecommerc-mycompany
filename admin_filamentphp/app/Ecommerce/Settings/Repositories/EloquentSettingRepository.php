<?php

namespace App\Ecommerce\Settings\Repositories;

use App\Ecommerce\Settings\Contracts\SettingRepositoryInterface;
use App\Models\Setting;
use App\Ecommerce\Core\Repositories\BaseRepository;

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
        $setting = $this->model->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value): bool
    {
        return (bool) $this->model->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
