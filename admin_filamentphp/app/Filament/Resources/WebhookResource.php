<?php

namespace App\Filament\Resources;

use App\Models\Webhook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\WebhookResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class WebhookResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Webhook::class;

    protected static ?string $navigationIcon = 'heroicon-o-rss';

    protected static ?string $slug = 'webhooks';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getModelLabel(): string
    {
        return trans('admin.webhooks.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin.webhooks.label');
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
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
                        Forms\Components\TextInput::make('name')
                            ->label(trans('admin.webhooks.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('url')
                            ->label(trans('admin.webhooks.url'))
                            ->url()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('secret')
                            ->label(trans('admin.webhooks.secret'))
                            ->password()
                            ->revealable()
                            ->default(fn() => \Illuminate\Support\Str::random(32))
                            ->required(),
                        Forms\Components\CheckboxList::make('events')
                            ->label(trans('admin.webhooks.events'))
                            ->options(trans('admin.webhooks.events_list'))
                            ->columns(2)
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label(trans('admin.webhooks.is_active'))
                            ->default(true),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('admin.webhooks.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->label(trans('admin.webhooks.url'))
                    ->searchable()
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(trans('admin.webhooks.is_active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebhooks::route('/'),
            'create' => Pages\CreateWebhook::route('/create'),
            'edit' => Pages\EditWebhook::route('/{record}/edit'),
        ];
    }
}
