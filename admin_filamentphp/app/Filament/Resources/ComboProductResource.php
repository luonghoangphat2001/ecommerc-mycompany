<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComboProductResource\Pages;
use App\Filament\Resources\ComboProductResource\RelationManagers;
use App\Models\ComboProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ComboProductResource extends Resource
{
    protected static ?string $model = ComboProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    public static function shouldRegisterResource(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(trans('admin.marketing.combo_info'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(trans('admin.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null
                            ),
                        Forms\Components\TextInput::make('slug')
                            ->label(trans('admin.slug'))
                            ->required()
                            ->unique(ComboProduct::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(trans('admin.sort_order'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\Textarea::make('description')
                            ->label(trans('admin.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make(trans('admin.marketing.combo_pricing'))
                    ->schema([
                        Forms\Components\TextInput::make('combo_price')
                            ->label(trans('admin.marketing.combo_price'))
                            ->numeric()
                            ->prefix('đ')
                            ->required(),
                        Forms\Components\TextInput::make('discount_percent')
                            ->label(trans('admin.marketing.discount_percent'))
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100),
                    ])->columns(2),

                Forms\Components\Section::make(trans('admin.marketing.combo_schedule'))
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label(trans('admin.is_active'))
                            ->default(true),
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label(trans('admin.start_date'))
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->label(trans('admin.end_date'))
                            ->nullable()
                            ->after('start_date'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('admin.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('combo_price')
                    ->label(trans('admin.marketing.combo_price'))
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_percent')
                    ->label(trans('admin.marketing.discount_percent'))
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(trans('admin.sort_order'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(trans('admin.is_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label(trans('admin.start_date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label(trans('admin.end_date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('admin.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(trans('admin.is_active')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComboProducts::route('/'),
            'create' => Pages\CreateComboProduct::route('/create'),
            'edit' => Pages\EditComboProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin.marketing.combo');
    }

    public static function getModelLabel(): string
    {
        return trans('admin.marketing.combo');
    }
}
