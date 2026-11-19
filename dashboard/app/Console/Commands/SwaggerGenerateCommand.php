<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SwaggerGenerateCommand extends Command
{
    protected $signature = 'swagger:generate';

    protected $description = 'Regenerate the OpenAPI documentation';

    public function handle(): int
    {
        return $this->call('l5-swagger:generate');
    }
}
