<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MailSettings extends Settings
{
    public ?string $email_from_address = null;
    public ?string $email_from_name = null;
    public ?string $email_host = null;
    public ?int $email_port = null;
    public ?string $email_username = null;
    public ?string $email_password = null;
    public ?string $email_encryption = null;
    public bool $use_queue_for_emails = false;

    public static function group(): string
    {
        return 'mail';
    }
}
