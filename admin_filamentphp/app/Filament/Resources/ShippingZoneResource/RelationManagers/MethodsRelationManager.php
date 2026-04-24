<?php

namespace App\Filament\Resources\ShippingZoneResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MethodsRelationManager extends RelationManager
{
    protected static string $relationship = 'methods';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label(trans('admin.shipping.method'))
                    ->options([
                        'flat_rate' => trans('admin.shipping.flat_rate'),
                        'free_shipping' => trans('admin.shipping.free_shipping'),
                        'api' => trans('admin.shipping.api_dynamic'),
                    ])
                    ->required()
                    ->native(false)
                    ->reactive(),
                Forms\Components\KeyValue::make('settings')
                    ->label(trans('admin.shipping.settings'))
                    ->schema([
                        // In a real app, you might use a dynamic fieldset based on 'type'
                    ])
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_enabled')
                    ->label(trans('admin.shipping.is_enabled'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\IconColumn::make('is_enabled')->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
