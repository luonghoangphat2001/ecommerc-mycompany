<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoyaltyPointResource\Pages;
use App\Filament\Resources\LoyaltyPointResource\RelationManagers;
use App\Models\LoyaltyPoint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;


class LoyaltyPointResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = LoyaltyPoint::class;

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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label(trans('admin.user.label'))
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('current_points')
                    ->numeric()
                    ->label(trans('admin.loyalty.current_points'))
                    ->required(),
                Forms\Components\TextInput::make('lifetime_points')
                    ->numeric()
                    ->label(trans('admin.loyalty.lifetime_points'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(trans('admin.user.label'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('current_points')
                    ->label(trans('admin.loyalty.current_points'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('lifetime_points')
                    ->label(trans('admin.loyalty.lifetime_points'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('admin.updated_at'))
                    ->dateTime(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListLoyaltyPoints::route('/'),
            'create' => Pages\CreateLoyaltyPoint::route('/create'),
            'edit' => Pages\EditLoyaltyPoint::route('/{record}/edit'),
        ];
    }
}
