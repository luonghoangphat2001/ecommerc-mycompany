<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Filament\Resources\Concerns\Translatable;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class PostResource extends Resource implements HasShieldPermissions
{
    use Translatable;

    protected static ?string $model = Post::class;

    protected static ?string $slug = 'blog/posts';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getModelLabel(): string
    {
        return trans('admin.post.label');
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 2;

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
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->label(trans('admin.title'))
                                    ->live(onBlur: true)
                                    ->maxLength(255)
                                    ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                                Forms\Components\TextInput::make('slug')
                                    ->disabled()
                                    ->label(trans('admin.slug'))
                                    ->dehydrated()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Post::class, 'slug', ignoreRecord: true),

                                Forms\Components\MarkdownEditor::make('content')
                                    ->required()
                                    ->label(trans('admin.content'))
                                    ->columnSpan('full'),

                                Forms\Components\Hidden::make('post_type')
                                    ->default('blog'),

                                Forms\Components\Select::make('author_id')
                                    ->relationship('author', 'name')
                                    ->searchable()
                                    ->label(trans('admin.author.label'))
                                    ->required(),

                                Forms\Components\Select::make('categories')
                                    ->multiple()
                                    ->relationship('categories', 'name', fn(Builder $query) => $query->type('post'))
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => $record->depth > 0 ? str_repeat('— ', $record->depth) . $record->name : $record->name)
                                    ->searchable()
                                    ->label(trans('admin.category.label'))
                                    ->required(),

                                Forms\Components\DatePicker::make('published_at')
                                    ->label(trans('admin.published_date')),

                                SpatieTagsInput::make('tags')->label(trans('admin.tags')),
                            ])
                    ])
                    ->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(trans('admin.image'))
                            ->schema([
                                CuratorPicker::make('image')
                                    ->label(trans('admin.image'))
                                    ->size('md'),
                            ]),
                        Forms\Components\Section::make('SEO')
                            ->schema([
                                Forms\Components\TextInput::make('seo_title')
                                    ->maxLength(60),
                                Forms\Components\Textarea::make('seo_description')
                                    ->maxLength(160),
                            ]),
                        Forms\Components\Section::make('Hiển thị')
                            ->schema([
                                Forms\Components\Toggle::make('is_visible')
                                    ->label('Công khai')
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                CuratorColumn::make('image')
                    ->label(trans('admin.image'))
                    ->size(50),

                Tables\Columns\TextColumn::make('title')
                    ->label(trans('admin.title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('author.name')
                    ->label(trans('admin.author.label'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('categories.name')
                    ->label(trans('admin.category.label'))
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(trans('admin.status'))
                    ->badge()
                    ->getStateUsing(fn(Post $record): string => $record->published_at?->isPast() ? 'Published' : 'Draft')
                    ->colors([
                        'success' => 'Published',
                    ]),

                Tables\Columns\TextColumn::make('published_at')
                    ->label(trans('admin.published_date'))
                    ->date(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\Split::make([
                            Components\Grid::make(2)
                                ->schema([
                                    Components\Group::make([
                                        Components\TextEntry::make('title')
                                            ->label(trans('admin.title')),
                                        Components\TextEntry::make('slug')
                                            ->label(trans('admin.slug')),
                                        Components\TextEntry::make('published_at')
                                            ->label(trans('admin.published_date'))
                                            ->badge()
                                            ->date()
                                            ->color('success'),
                                    ]),
                                    Components\Group::make([
                                        Components\TextEntry::make('author.name')
                                            ->label(trans('admin.author.label')),
                                        Components\TextEntry::make('categories.name')
                                            ->label(trans('admin.category.label'))
                                            ->badge(),
                                        Components\SpatieTagsEntry::make('tags')
                                            ->label(trans('admin.tags')),
                                    ]),
                                ]),
                            Components\ImageEntry::make('image')
                                ->hiddenLabel()
                                ->grow(false)
                            ->getStateUsing(fn($record) => app(\App\Services\PostService::class)->getFeaturedImageUrl($record)),
                        ])->from('lg'),
                    ]),
                Components\Section::make(trans('admin.content'))
                    ->schema([
                        Components\TextEntry::make('content')
                            ->prose()
                            ->markdown()
                            ->hiddenLabel(),
                    ])
                    ->collapsible(),
            ]);
    }

    /** @return Builder<Post> */
    public static function getEloquentQuery(): Builder
    {
        return app(\App\Contracts\Services\PostServiceInterface::class)->getTableQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
            'view' => Pages\ViewPost::route('/{record}'),
        ];
    }
}
