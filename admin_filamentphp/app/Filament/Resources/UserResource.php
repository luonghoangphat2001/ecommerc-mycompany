<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Shield\Facades\Shield;
use Illuminate\Database\Eloquent\Model;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';


    public static function getModelLabel(): string
    {
        return trans('admin.user.label'); // Dịch "Category"
    }

    protected static ?int $navigationSort = 11;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            // 
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->nullable()
                    ->password()

                    ->afterStateUpdated(function ($state) {
                        return $state; // Trả lại trạng thái sau khi cập nhật
                    }),

                Forms\Components\MarkdownEditor::make('bio')
                    ->label(trans('admin.bio'))
                    ->columnSpan('full'),

                Forms\Components\TextInput::make('github_handle')
                    ->label(trans('admin.github_handle'))
                    ->maxLength(255),

                Forms\Components\TextInput::make('twitter_handle')
                    ->label(trans('admin.twitter_handle'))
                    ->maxLength(255),

                // Multiselect field for roles
                Forms\Components\Select::make('roles')
                    ->label(trans('admin.common.roles'))
                    ->options(function () {
                        return Shield::getRoles()->pluck('name', 'id')->toArray();
                    })
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->required(),

                Forms\Components\Section::make('Metadata')
                    ->schema([
                        Forms\Components\Repeater::make('meta')
                            ->relationship('meta')
                            ->label('Metadata')
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->label('Key')
                                    ->required(),
                                Forms\Components\TextInput::make('value')
                                    ->label('Value')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                //
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('email')->sortable()->searchable(),

                Tables\Columns\TextColumn::make('github_handle')
                    ->icon('icon-github')
                    ->label('GitHub')
                    ->alignLeft(),

                Tables\Columns\TextColumn::make('twitter_handle')
                    ->icon('icon-twitter')
                    ->label('Twitter')
                    ->alignLeft(),
                Tables\Columns\TextColumn::make('roles.name')->label(trans('admin.common.roles'))->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    /** @return Builder<User> */
    public static function getEloquentQuery(): Builder
    {
        return app(\App\Contracts\Services\CustomerServiceInterface::class)->getTableQuery();
    }
}
