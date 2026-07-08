<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class EmailSettings extends Settings
{
    public string $sender_name;
    public string $sender_email;
    public string $base_color;
    public array $notifications; // e.g. ['new_order' => ['enabled' => true, 'recipients' => '...', 'template' => '...']]

    public static function group(): string
    {
        return 'emails';
    }
}
