<?php

namespace App\Filament\Resources;

use App\Models\WebhookLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\WebhookLogResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class WebhookLogResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = WebhookLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $slug = 'webhook-logs';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getModelLabel(): string
    {
        return trans('admin.webhooks.log_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin.webhooks.log_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('admin.settings.advanced');
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('webhook_id')
                            ->relationship('webhook', 'name')
                            ->label(trans('admin.webhooks.label'))
                            ->disabled(),
                        Forms\Components\TextInput::make('event')
                            ->label(trans('admin.webhooks.events'))
                            ->disabled(),
                        Forms\Components\TextInput::make('status')
                            ->label(trans('admin.status'))
                            ->disabled(),
                        Forms\Components\TextInput::make('duration')
                            ->label(trans('admin.duration'))
                            ->suffix('ms')
                            ->disabled(),
                        Forms\Components\KeyValue::make('payload')
                            ->label(trans('admin.payload'))
                            ->columnSpanFull()
                            ->disabled(),
                        Forms\Components\KeyValue::make('response')
                            ->label(trans('admin.response'))
                            ->columnSpanFull()
                            ->disabled(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('webhook.name')
                    ->label(trans('admin.webhooks.label'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event')
                    ->label(trans('admin.webhooks.events'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(trans('admin.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'delivered' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('duration')
                    ->label(trans('admin.duration'))
                    ->suffix('ms')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('admin.common.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebhookLogs::route('/'),
            'view' => Pages\ViewWebhookLog::route('/{record}'),
        ];
    }
}
