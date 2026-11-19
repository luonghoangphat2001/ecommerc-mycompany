<?php

namespace App\Support;

use Illuminate\Contracts\Foundation\Application;

final class Localization
{
    public function __construct(private readonly Application $application) {}

    public function current(): string
    {
        return (string) $this->application->getLocale();
    }

    public function set(string $locale): void
    {
        $this->application->setLocale($locale);
    }

    public static function value(array $values, ?string $locale = null): mixed
    {
        $locale ??= app()->getLocale() ?: 'vi';

        return $values[$locale] ?? $values['vi'] ?? $values['en'] ?? (! empty($values) ? reset($values) : null);
    }
}
