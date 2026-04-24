<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function resolveRecord(int | string $key): Order
    {
        return app(\App\Contracts\Services\OrderServiceInterface::class)->getFullOrder($key);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('send_user_email')
                    ->label(trans('admin.order.send_email_user'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        app(\App\Contracts\Services\OrderServiceInterface::class)->sendOrderConfirmationMail($this->record);
                        \Filament\Notifications\Notification::make()
                            ->title(trans('admin.order.send_email_user_success'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Order $record) => app(\App\Contracts\Services\OrderServiceInterface::class)->hasPendingPayments($record)),
                Actions\Action::make('confirm_payment')
                    ->label(trans('admin.order.confirm_payment'))
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        app(\App\Contracts\Services\OrderServiceInterface::class)->confirmPayment($record);
                        app(\App\Contracts\Services\OrderServiceInterface::class)->sendOrderConfirmationMail($this->record);
                        \Filament\Notifications\Notification::make()
                            ->title(trans('admin.order.send_email_user_success'))
                            ->success()
                            ->send();
                        \Filament\Notifications\Notification::make()->title(trans('admin.order.payment_confirmed'))->success()->send();
                    })
                    ->visible(fn(Order $record) => app(\App\Contracts\Services\OrderServiceInterface::class)->hasPendingPayments($record)),

                Actions\Action::make('cancel_order')
                    ->label(trans('admin.order.cancel_order'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label(trans('admin.order.cancellation_reason'))
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data) {
                        app(\App\Contracts\Services\OrderServiceInterface::class)->cancel($record, $data['reason']);
                        \Filament\Notifications\Notification::make()->title(trans('admin.order.cancelled_success'))->success()->send();
                    })
                    ->visible(fn(Order $record) => !in_array($record->status, [\App\Enums\OrderStatus::Cancelled, \App\Enums\OrderStatus::Completed, \App\Enums\OrderStatus::Refunded])),

                Actions\Action::make('refund_order')
                    ->label(trans('admin.order.refund_order'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label(trans('admin.order.refund_reason'))
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data) {
                        app(\App\Contracts\Services\OrderServiceInterface::class)->refund($record, $data['reason']);
                        \Filament\Notifications\Notification::make()->title(trans('admin.order.refunded_success'))->success()->send();
                    })
                    ->visible(fn(Order $record) => $record->status === \App\Enums\OrderStatus::Completed),
            ])
                ->label(trans('admin.actions'))
                ->icon('heroicon-m-chevron-down')
                ->color('primary')
                ->button(),

            Actions\ActionGroup::make([
                Actions\Action::make('print_invoice')
                    ->label(trans('admin.order.print_invoice'))
                    ->icon('heroicon-o-printer')
                    ->action(fn(Order $record) => app(\App\Contracts\Services\OrderExportServiceInterface::class)->exportInvoice($record)),

                Actions\Action::make('print_delivery_note')
                    ->label(trans('admin.order.print_delivery_note'))
                    ->icon('heroicon-o-truck')
                    ->action(fn(Order $record) => app(\App\Contracts\Services\OrderExportServiceInterface::class)->exportDeliveryNote($record)),
            ])
                ->label(trans('admin.order.export'))
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->button(),

            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return array_merge(parent::getFormActions(), [
            Actions\Action::make('recalculate')
                ->label(trans('admin.order.recalculate_totals'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription(trans('admin.order.recalculate_totals_desc'))
                ->hidden(fn(Order $record) => !in_array($record->status, [\App\Enums\OrderStatus::Pending, \App\Enums\OrderStatus::Processing]))
                ->action(function (Order $record) {
                    app(\App\Contracts\Services\OrderServiceInterface::class)->recalculateTotals($record);

                    \Filament\Notifications\Notification::make()
                        ->title(trans('admin.order.recalculated_success'))
                        ->success()
                        ->send();
                    $this->refreshFormData(['subtotal', 'total']);
                }),
        ]);
    }
}
