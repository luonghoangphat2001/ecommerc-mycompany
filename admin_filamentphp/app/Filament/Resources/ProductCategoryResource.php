<?php

namespace App\Filament\Resources;


use App\Models\ProductCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Filament\Resources\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\ProductCategoryResource\Pages;

class ProductCategoryResource extends Resource implements HasShieldPermissions
{
    use Translatable;

    protected static ?string $model = ProductCategory::class;

    // protected static ?string $cluster = Products::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return trans('admin.category.label');
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
                                    ->unique(ProductCategory::class, 'slug', ignoreRecord: true),
                            ]),

                        Forms\Components\Select::make('parent_id')
                            ->label(trans('admin.category_parent'))
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query, ?ProductCategory $record) =>
                                $record ? $query->where('id', '!=', $record->id) : $query
                            )
                            ->getOptionLabelFromRecordUsing(fn(Model $record) => $record->depth > 0 ? str_repeat('— ', $record->depth) . $record->name : $record->name)
                            ->searchable()
                            ->placeholder('Select parent category'),

                        Forms\Components\Toggle::make('is_visible')

                            ->label(trans('admin.is_visible'))
                            ->default(true),

                        Forms\Components\MarkdownEditor::make('description')
                            ->label(trans('admin.description')),
                    ])
                    ->columnSpan(['lg' => fn(?ProductCategory $record) => $record === null ? 3 : 2]),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label(trans('admin.created_at'))

                            ->content(fn(ProductCategory $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label(trans('admin.updated_at'))
                            ->content(fn(ProductCategory $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?ProductCategory $record) => $record === null),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('admin.name'))
                    ->formatStateUsing(fn(ProductCategory $record) => $record->depth > 0 ? str_repeat('— ', $record->depth) . $record->name : $record->name)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(trans('admin.is_visible'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('admin.created_at'))
                    ->date()
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => app(\App\Contracts\Services\ProductCategoryServiceInterface::class)->applyTreeSorting($query))
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make()

            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\ProductCategoryResource\RelationManagers\ProductsRelationManager::class,
        ];
    }

    /** @return Builder<ProductCategory> */
    public static function getEloquentQuery(): Builder
    {
        return app(\App\Contracts\Services\ProductCategoryServiceInterface::class)->getTableQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
