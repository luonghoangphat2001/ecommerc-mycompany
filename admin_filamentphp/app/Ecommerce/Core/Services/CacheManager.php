<?php

namespace App\Ecommerce\Core\Services;

use Illuminate\Support\Facades\Cache;
use Closure;

class CacheManager
{
    /**
     * Cache system settings.
     *
     * @param string $key
     * @param Closure $callback
     * @return mixed
     */
    public function rememberSettings(string $key, Closure $callback)
    {
        return Cache::tags(['settings'])->rememberForever($key, $callback);
    }

    /**
     * Cache shipping/tax rules.
     *
     * @param string $key
     * @param Closure $callback
     * @return mixed
     */
    public function rememberRules(string $key, Closure $callback)
    {
        return Cache::tags(['rules'])->remember($key, 86400, $callback); // 24 hours
    }

    /**
     * Invalidate specific tags.
     *
     * @param array $tags
     * @return void
     */
    public function invalidate(array $tags): void
    {
        Cache::tags($tags)->flush();
    }
}
