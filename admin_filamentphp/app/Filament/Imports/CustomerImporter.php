<?php

namespace App\Filament\Imports;

use App\Filament\Imports\BaseImporter;
use App\Ecommerce\Customer\Contracts\CustomerServiceInterface;
use App\Models\Customer;
use Filament\Actions\Imports\ImportColumn;

class CustomerImporter extends BaseImporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('John Doe'),
            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255'])
                ->example('john@example.com'),
            ImportColumn::make('gender')
                ->rules(['nullable', 'in:male,female,other'])
                ->example('male'),
            ImportColumn::make('phone')
                ->rules(['nullable', 'max:50'])
                ->example('+84901234567'),
            ImportColumn::make('birthday')
                ->rules(['nullable', 'date'])
                ->example('1990-01-15'),
        ];
    }

    public function resolveRecord(): ?Customer
    {
        return app(CustomerServiceInterface::class)->firstOrNew([
            'email' => $this->data['email'],
        ]);
    }
}
