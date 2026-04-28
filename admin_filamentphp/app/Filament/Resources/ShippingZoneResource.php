<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingZoneResource\Pages;
use App\Filament\Resources\ShippingZoneResource\RelationManagers;
use App\Models\ShippingZone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShippingZoneResource extends Resource
{
    protected static ?string $model = ShippingZone::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';



    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(trans('admin.shipping.zone_area'))
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label(trans('admin.name'))
                                            ->placeholder('e.g. Vietnam South')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('sort')
                                            ->label(trans('admin.associations'))
                                            ->required()
                                            ->numeric()
                                            ->default(0),
                                    ]),

                                Forms\Components\Repeater::make('locations')
                                    ->label(trans('admin.shipping.zone_area'))
                                    ->schema([
                                        Forms\Components\Select::make('country')
                                            ->label(trans('admin.country'))
                                            ->options(config('countries'))
                                            ->searchable()
                                            ->preload()
                                            ->reactive()
                                            ->afterStateUpdated(fn (Forms\Set $set) => $set('provinces', null))
                                            ->required(),

                                        Forms\Components\Select::make('provinces')
                                            ->label(trans('admin.state'))
                                            ->multiple()
                                            ->searchable()
                                            ->options(function (Forms\Get $get) {
                                                $country = $get('country');
                                                return $country ? config("states.{$country}", []) : [];
                                            })
                                            ->placeholder(trans('admin.all_provinces'))
                                            ->hint(trans('admin.leave_empty_for_all')),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => ($state['country'] ?? null) ? (config("countries.{$state['country']}") . ' (' . count($state['provinces'] ?? []) . ' ' . trans('admin.state') . ')') : null)
                                    ->collapsible()
                                    ->defaultItems(1)
                                    ->columns(2)
                                    ->grid(1),
                            ]),
                        
                        // Note: Methods are handled by RelationManager, but we can add a placeholder 
                        // Tab here if we were building a custom page. For now, we follow standard Resource layout.
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sort')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MethodsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingZones::route('/'),
            'create' => Pages\CreateShippingZone::route('/create'),
            'view' => Pages\ViewShippingZone::route('/{record}'),
            'edit' => Pages\EditShippingZone::route('/{record}/edit'),
        ];
    }

    /** @return Builder<ShippingZone> */
    public static function getEloquentQuery(): Builder
    {
        return app(\App\Ecommerce\Shipping\Contracts\ShippingServiceInterface::class)->getShippingZoneTableQuery();
    }
}
