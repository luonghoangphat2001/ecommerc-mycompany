<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoyaltyLogResource\Pages;
use App\Filament\Resources\LoyaltyLogResource\RelationManagers;
use App\Models\LoyaltyLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;


class LoyaltyLogResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = LoyaltyLog::class;

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
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('points_changed')
                    ->label(trans('admin.loyalty.points_changed'))
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('action_type')
                    ->label(trans('admin.loyalty.points_changed'))
                    ->options([
                        'earn' => trans('admin.loyalty.earn'),
                        'redeem' => trans('admin.loyalty.redeem'),
                        'refund' => trans('admin.loyalty.refund'),
                        'adjustment' => trans('admin.loyalty.adjustment'),
                    ])
                    ->required(),
                Forms\Components\Select::make('order_id')
                    ->relationship('order', 'number')
                    ->label(trans('admin.order.label'))
                    ->searchable(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(trans('admin.user.label'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('points_changed')
                    ->label(trans('admin.loyalty.points_changed'))
                    ->badge()
                    ->color(fn(int $state) => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('action_type')
                    ->label(trans('admin.type'))
                    ->badge(),
                Tables\Columns\TextColumn::make('order_id')
                    ->label(trans('admin.order.id'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('admin.created_at'))
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action_type')
                    ->options([
                        'earn' => 'Earn',
                        'redeem' => 'Redeem',
                        'refund' => 'Refund',
                        'adjustment' => 'Adjustment',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoyaltyLogs::route('/'),
        ];


    }

}
