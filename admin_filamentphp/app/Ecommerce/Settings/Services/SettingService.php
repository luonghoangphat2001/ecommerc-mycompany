<?php

namespace App\Ecommerce\Settings\Services;

use App\Ecommerce\Settings\Contracts\SettingRepositoryInterface;
use App\Ecommerce\Settings\Contracts\SettingServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class SettingService implements SettingServiceInterface
{
    /**
     * @var SettingRepositoryInterface
     */
    protected $settingRepository;

    /**
     * SettingService constructor.
     *
     * @param SettingRepositoryInterface $settingRepository
     */
    public function __construct(SettingRepositoryInterface $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllSettings(): Collection
    {
        return $this->settingRepository->all();
    }

    /**
     * @inheritDoc
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settingRepository->get($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function updateSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->settingRepository->set($key, $value);
        }
    }
}
