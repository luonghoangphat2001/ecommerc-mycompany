<?php

namespace App\Traits;

use App\Support\Localization;

trait HasLocalizedDisplayName
{
    public function localizedDisplayName(string $fallbackLabel): string
    {
        $locale = app()->getLocale() ?: 'vi';
        $name = $this->getTranslation('name', $locale) ?: $this->getTranslation('name', 'vi');

        if (empty($name) && is_array($this->name)) {
            $name = Localization::value($this->name, $locale) ?? '';
        }

        return (string) ($name ?: ($this->slug ?: $fallbackLabel . ' #' . $this->getKey()));
    }

    public function parentBreadcrumbs(): string
    {
        $parts = [];
        $current = $this->parent;

        while ($current) {
            array_unshift($parts, $current->display_name);
            $current = $current->parent;
        }

        return empty($parts) ? '—' : implode(' > ', $parts);
    }
}
