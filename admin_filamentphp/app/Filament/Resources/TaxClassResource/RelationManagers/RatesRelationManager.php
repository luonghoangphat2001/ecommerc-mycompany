<?php

namespace App\Filament\Resources\TaxClassResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RatesRelationManager extends RelationManager
{
    protected static string $relationship = 'rates';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                \App\Forms\Components\AddressForm::make('regional_settings')
                    ->statePath('')
                    ->regional()
                    ->onlyFields(['country_code', 'state_id', 'city_id'])
                    ->fieldMap([
                        'country_code' => 'country',
                        'state_id' => 'state',
                        'city_id' => 'city',
                    ]),

                Forms\Components\TextInput::make('rate')
                    ->label(trans('admin.tax.rate'))
                    ->required()
                    ->numeric()
                    ->suffix('%'),
                Forms\Components\TextInput::make('priority')
                    ->label(trans('admin.tax.priority'))
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('country'),
                Tables\Columns\TextColumn::make('rate')->suffix('%'),
                Tables\Columns\TextColumn::make('priority'),
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
