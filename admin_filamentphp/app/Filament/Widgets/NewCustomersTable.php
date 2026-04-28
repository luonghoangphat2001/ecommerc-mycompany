<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Traits\HasCurrencyFormat;
use Filament\Forms;
use Filament\Tables\Actions\Action;

class NewCustomersTable extends BaseWidget
{
    use HasCurrencyFormat;

    public function getColumnSpan(): int | string | array
    {
        return [
            'xl' => 9,
            'md' => 12,
            'sm' => 6
        ];
    }

    protected static ?string $heading = null;

    public function __construct()
    {
        static::$heading = trans('admin.chart.new_customers');
    }

    public function table(Table $table): Table
    {
        $analyticsService = app(\App\Ecommerce\Analytics\Contracts\AnalyticsServiceInterface::class);

        return $table
            ->query(fn () => 
                app(\App\Ecommerce\Analytics\Contracts\AnalyticsServiceInterface::class)->getFilteredNewCustomersQuery(request('date_from'), request('date_to'))
                    ->orderByDesc('latest_order')
            )
            ->defaultPaginationPageOption(5)

            ->columns([
                Tables\Columns\TextColumn::make('customer_display_name')
                    ->label(trans('admin.order.customer'))
                    ->getStateUsing(fn($record) => $record->customer_name ?: trans('admin.order.guest'))
                    ->searchable(['shop_order_addresses.first_name', 'shop_order_addresses.last_name', 'shop_order_addresses.email'])
                    ->sortable()
                    ->alignLeft(),
                Tables\Columns\TextColumn::make('total_spent')
                    ->label(trans('admin.order.total_price'))
                    ->formatStateUsing(fn($state) => self::formatMoney($state))
                    ->sortable()
                    ->alignRight(),

            ])
            ->reorderRecordsTriggerAction(
                fn(Action $action, bool $isReordering) => $action
                    ->button()
                    ->label($isReordering ? trans('admin.reordering.disable') : trans('admin.reordering.enable')),
            )
            ->filters(
                [
                    Tables\Filters\Filter::make('date_range')
                        ->form([
                            Forms\Components\DatePicker::make('start_date')
                                ->label(trans('admin.start_date'))
                                ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y'))
                                ->columnSpanFull(),
                            Forms\Components\DatePicker::make('end_date')
                                ->label(trans('admin.end_date'))
                                ->placeholder(fn($state): string => now()->format('M d, Y'))
                                ->columnSpanFull(),
                        ])
                        ->query(function ($query, $data) {
                            app(\App\Ecommerce\Analytics\Contracts\AnalyticsServiceInterface::class)->applyNewCustomersFilter($query, $data['start_date'] ?? null, $data['end_date'] ?? null);
                        }),

                ],

            )
            ->actions([
                Tables\Actions\Action::make(trans('admin.open'))
                    ->url(fn($record): string => UserResource::getUrl('edit', ['record' => $record->user_id]))
                    ->visible(fn($record) => $record->user_id !== null),
            ]);
    }
}
