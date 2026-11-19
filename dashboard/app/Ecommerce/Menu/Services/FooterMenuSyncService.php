<?php

namespace App\Ecommerce\Menu\Services;

use App\Ecommerce\Menu\Contracts\MenuServiceInterface;
use App\Settings\FooterSettings;

class FooterMenuSyncService
{
    public function __construct(
        private readonly FooterSettings $footerSettings,
        private readonly MenuServiceInterface $menus,
    ) {}

    public function syncByMenuId(?int $menuId): void
    {
        if (! $menuId) {
            return;
        }

        try {
            $footerMenu = $this->menus->getMenuBySlug($this->footerSettings->menu_handle ?: 'footer-menu');
            if (! $footerMenu || (int) $footerMenu->id !== $menuId) {
                return;
            }

            $this->footerSettings->links = $footerMenu->load('items')->items
                ->map(static fn ($item) => [
                    'id' => $item->id,
                    'title' => is_array($item->title) ? ($item->title['vi'] ?? reset($item->title)) : $item->title,
                    'url' => $item->url,
                    'target' => $item->target ?? '_self',
                    'order' => $item->order ?? 0,
                ])->values()->all();
            $this->footerSettings->save();
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function syncFromHandle(?string $handle): void
    {
        $menu = $this->menus->getMenuBySlug($handle ?: 'footer-menu');
        $this->footerSettings->links = $menu?->items->map(static fn ($item) => [
            'id' => $item->id,
            'title' => is_array($item->title) ? ($item->title['vi'] ?? reset($item->title)) : $item->title,
            'url' => $item->url,
            'target' => $item->target ?? '_self',
            'order' => $item->order ?? 0,
        ])->values()->all() ?? [];
        $this->footerSettings->menu_handle = $handle ?: 'footer-menu';
        $this->footerSettings->save();
    }
}
