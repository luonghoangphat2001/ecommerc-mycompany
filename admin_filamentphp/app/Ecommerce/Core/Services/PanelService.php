<?php

namespace App\Ecommerce\Core\Services;

use App\Ecommerce\Core\Contracts\PanelServiceInterface;
use App\Settings\DBSettings;
use Illuminate\Support\Facades\Schema;

class PanelService implements PanelServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getBrandName(string $default = 'Admin Panel'): string
    {
        try {
            // Check if settings table exists to avoid migration errors
            if (!Schema::hasTable('settings')) {
                return $default;
            }
            return app(DBSettings::class)->name ?? $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * @inheritDoc
     */
    public function getTimezone(string $default = 'UTC'): string
    {
        try {
            if (!Schema::hasTable('settings')) {
                return $default;
            }
            return app(DBSettings::class)->timezone ?? $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}
