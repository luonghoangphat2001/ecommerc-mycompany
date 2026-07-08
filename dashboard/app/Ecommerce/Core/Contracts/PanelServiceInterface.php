<?php

namespace App\Ecommerce\Core\Contracts;

interface PanelServiceInterface
{
    /**
     * Get the brand name for the panel.
     *
     * @param string $default
     * @return string
     */
    public function getBrandName(string $default = 'Admin Panel'): string;

    /**
     * Get the timezone for the panel.
     *
     * @param string $default
     * @return string
     */
    public function getTimezone(string $default = 'UTC'): string;
}
