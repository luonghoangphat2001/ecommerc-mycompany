<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaxClassResource\Pages;
use App\Filament\Resources\TaxClassResource\RelationManagers;
use App\Models\TaxClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaxClassResource extends Resource
{
    protected static ?string $model = TaxClass::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';



    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Split::make([
                            Forms\Components\TextInput::make('name')
                                ->label(trans('admin.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                            Forms\Components\TextInput::make('slug')
                                ->label(trans('admin.slug'))
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->maxLength(255),
                        ]),
                    ]),

                Forms\Components\Section::make(trans('admin.tax.rate'))
                    ->schema([
                        Forms\Components\Repeater::make('rates')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(trans('admin.name'))
                                    ->required()
                                    ->columnSpan(3),
                                Forms\Components\TextInput::make('rate')
                                    ->label(trans('admin.tax.rate') . ' (%)')
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('priority')
                                    ->label(trans('admin.tax.priority'))
                                    ->numeric()
                                    ->default(1)
                                    ->columnSpan(1),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel(trans('admin.shipping.add_rate'))
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
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
            RelationManagers\RatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaxClasses::route('/'),
            'create' => Pages\CreateTaxClass::route('/create'),
            'view' => Pages\ViewTaxClass::route('/{record}'),
            'edit' => Pages\EditTaxClass::route('/{record}/edit'),
        ];
    }

    /** @return Builder<TaxClass> */
    public static function getEloquentQuery(): Builder
    {
        return app(\App\Contracts\Services\TaxServiceInterface::class)->getTaxClassTableQuery();
    }
}
