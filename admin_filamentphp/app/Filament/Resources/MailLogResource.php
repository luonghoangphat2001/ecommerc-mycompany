<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailLogResource\Pages;
use App\Models\MailLog;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MailLogResource extends Resource
{
    protected static ?string $model = MailLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';

    protected static ?int $navigationSort = 13;

    public static function getLabel(): string
    {
        return trans('admin.mail_log.label');
    }

    public static function getPluralLabel(): string
    {
        return trans('admin.mail_log.plural_label');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->label(trans('admin.mail_log.status'))
                    ->badge()
                    ->state(fn($record) => $record->status ?? 'sent')
                    ->color(fn(string $state): string => match (strtolower($state)) {
                        'delivered' => 'success',
                        'sent' => 'info',
                        'bounced' => 'danger',
                        'complaint' => 'warning',
                        'opened' => 'success',
                        default => 'info',
                    })
                    ->formatStateUsing(fn(string $state): string => Str::headline($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label(trans('admin.mail_log.subject'))
                    ->limit(40)
                    ->tooltip(fn(Tables\Columns\TextColumn $column): ?string => $column->getState())
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('to')
                    ->label(trans('admin.mail_log.to'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('admin.mail_log.date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make(trans('admin.mail_log.section_general'))
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->label(trans('admin.mail_log.status'))
                            ->badge()
                            ->state(fn($record) => $record->status ?? 'sent')
                            ->color(fn(string $state): string => match (strtolower($state)) {
                                'delivered' => 'success',
                                'sent' => 'info',
                                'bounced' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Ngày gửi')
                            ->datetime(),
                        Infolists\Components\TextEntry::make('from')
                            ->label(trans('admin.mail_log.from')),
                        Infolists\Components\TextEntry::make('to')
                            ->label(trans('admin.mail_log.to')),
                        Infolists\Components\TextEntry::make('subject')
                            ->label(trans('admin.common.visibility'))
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('body')
                            ->label(null)
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make(trans('admin.mail_log.section_tech'))
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('message_id')
                            ->label(trans('admin.common.roles')),
                        Infolists\Components\TextEntry::make('headers')
                            ->label('Headers')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('data')
                            ->label('Metadata')
                            ->formatStateUsing(fn($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\MailLogResource\Pages\ListMailLogs::route('/'),
        ];
    }
}
