<?php

namespace App\Livewire\Settings;

use App\Contracts\Repositories\WebhookLogRepositoryInterface;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class WebhookLogViewer extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(app(WebhookLogRepositoryInterface::class)->query())
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('webhook.name')
                    ->label(trans('admin.webhooks.webhook'))
                    ->sortable(),
                TextColumn::make('event')
                    ->label(trans('admin.webhooks.event'))
                    ->badge(),
                TextColumn::make('status')
                    ->label(trans('admin.webhooks.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'delivered' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('duration')
                    ->label(trans('admin.webhooks.duration'))
                    ->suffix('ms'),
                TextColumn::make('created_at')
                    ->label(trans('admin.webhooks.created_at'))
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    public function render()
    {
        return view('livewire.settings.webhook-log-viewer');
    }
}
