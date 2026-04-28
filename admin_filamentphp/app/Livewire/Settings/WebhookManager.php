<?php

namespace App\Livewire\Settings;

use App\Ecommerce\Analytics\Contracts\WebhookRepositoryInterface;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Filament\Forms;

class WebhookManager extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('view_any_webhook'), 403);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(app(WebhookRepositoryInterface::class)->query())
            ->columns([
                TextColumn::make('name')
                    ->label(trans('admin.webhooks.name'))
                    ->searchable(),
                TextColumn::make('url')
                    ->label(trans('admin.webhooks.url'))
                    ->limit(30),
                IconColumn::make('is_active')
                    ->label(trans('admin.webhooks.is_active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(trans('admin.common.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make()
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label(trans('admin.webhooks.name'))
                            ->required(),
                        Forms\Components\TextInput::make('url')
                            ->label(trans('admin.webhooks.url'))
                            ->url()
                            ->required(),
                        Forms\Components\TextInput::make('secret')
                            ->label(trans('admin.webhooks.secret'))
                            ->password()
                            ->revealable()
                            ->default(fn() => \Illuminate\Support\Str::random(32)),
                        Forms\Components\CheckboxList::make('events')
                            ->label(trans('admin.webhooks.events'))
                            ->options(trans('admin.webhooks.events_list'))
                            ->columns(2)
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label(trans('admin.webhooks.is_active'))
                            ->default(true),
                    ]),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label(trans('admin.webhooks.name'))
                            ->required(),
                        Forms\Components\TextInput::make('url')
                            ->label(trans('admin.webhooks.url'))
                            ->url()
                            ->required(),
                        Forms\Components\TextInput::make('secret')
                            ->label(trans('admin.webhooks.secret'))
                            ->password()
                            ->revealable()
                            ->default(fn() => \Illuminate\Support\Str::random(32)),
                        Forms\Components\CheckboxList::make('events')
                            ->label(trans('admin.webhooks.events'))
                            ->options(trans('admin.webhooks.events_list'))
                            ->columns(2)
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label(trans('admin.webhooks.is_active'))
                            ->default(true),
                    ]),
            ]);
    }

    public function render()
    {
        return view('livewire.settings.webhook-manager');
    }
}
