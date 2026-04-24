<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Akaunting\Money\Currency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('admin.payments.label');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reference')
                    ->label(trans('admin.payments.reference'))
                    ->columnSpan('full')
                    ->required(),

                Forms\Components\TextInput::make('amount')
                    ->label(trans('admin.payments.amount'))
                    ->numeric()
                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                    ->required(),

                Forms\Components\Select::make('currency')
                    ->label(trans('admin.order.currency'))
                    ->options(collect(Currency::getCurrencies())->mapWithKeys(fn($item, $key) => [$key => data_get($item, 'name')]))
                    ->searchable()
                    ->required(),

                Forms\Components\ToggleButtons::make('provider')
                    ->inline()
                    ->label(trans('admin.payments.provider'))
                    ->grouped()
                    ->options([
                        'stripe' =>  trans('admin.payments.stripe'),
                        'paypal' => trans('admin.payments.paypal'),
                    ])
                    ->required(),

                Forms\Components\ToggleButtons::make('method')
                    ->inline()
                    ->label(trans('admin.payments.method'))
                    ->options([
                        'credit_card' =>  trans('admin.payments.credit_card'),
                        'bank_transfer' => trans('admin.payments.bank_transfer'),
                        'paypal' => trans('admin.payments.paypal'),
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColumnGroup::make(trans('admin.payments.details'))
                    ->columns([
                        Tables\Columns\TextColumn::make('reference')
                            ->label(trans('admin.payments.reference'))
                            ->searchable(),

                        Tables\Columns\TextColumn::make('amount')
                            ->label(trans('admin.payments.amount'))
                            ->sortable()
                            ->money(fn($record) => $record->currency),
                    ]),

                Tables\Columns\ColumnGroup::make(trans('admin.payments.context'))
                    ->columns([
                        Tables\Columns\TextColumn::make('provider')
                            ->label(trans('admin.payments.provider'))
                            ->formatStateUsing(fn($state) => Str::headline($state))
                            ->sortable(),

                        Tables\Columns\TextColumn::make('method')
                            ->label(trans('admin.payments.method'))
                            ->formatStateUsing(fn($state) => Str::headline($state))
                            ->sortable(),
                    ]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label(trans('admin.create')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
