<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource as Products;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\BrandResource\Pages;

class BrandResource extends Resource implements HasShieldPermissions
{
    use Translatable;
    protected static ?string $model = Brand::class;

    // protected static ?string $cluster = Products::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 6;

    public static function getModelLabel(): string
    {
        return trans('admin.brand.label');
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
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->label(trans('admin.name'))
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                                Forms\Components\TextInput::make('slug')
                                    ->disabled()
                                    ->dehydrated()
                                    ->label(trans('admin.slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Brand::class, 'slug', ignoreRecord: true),
                            ]),
                        Forms\Components\TextInput::make('website')
                            ->required()
                            ->label(trans('admin.website'))
                            ->maxLength(255)
                            ->url(),

                        Forms\Components\Toggle::make('is_visible')
                            ->label(trans('admin.is_visible'))
                            ->default(true),

                        Forms\Components\MarkdownEditor::make('description')
                            ->label(trans('admin.description')),
                    ])
                    ->columnSpan(['lg' => fn(?Brand $record) => $record === null ? 3 : 2]),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label(trans('admin.created_at'))
                            ->content(fn(Brand $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label(trans('admin.updated_at'))
                            ->content(fn(Brand $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?Brand $record) => $record === null),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('admin.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('website')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(trans('admin.is_visible'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('admin.updated_at'))
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make()

            ])
            ->defaultSort('sort')
            ->reorderable('sort');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\BrandResource\RelationManagers\ProductsRelationManager::class,
            \App\Filament\Resources\BrandResource\RelationManagers\AddressesRelationManager::class,
        ];
    }

    /** @return Builder<Brand> */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return app(\App\Contracts\Services\BrandServiceInterface::class)->getTableQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
