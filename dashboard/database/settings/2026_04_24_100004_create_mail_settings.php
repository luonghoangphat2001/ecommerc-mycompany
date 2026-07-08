<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('mail.email_from_address', null);
        $this->migrator->add('mail.email_from_name', null);
        $this->migrator->add('mail.email_host', null);
        $this->migrator->add('mail.email_port', null);
        $this->migrator->add('mail.email_username', null);
        $this->migrator->add('mail.email_password', null);
        $this->migrator->add('mail.email_encryption', null);
        $this->migrator->add('mail.use_queue_for_emails', false);
    }
};
