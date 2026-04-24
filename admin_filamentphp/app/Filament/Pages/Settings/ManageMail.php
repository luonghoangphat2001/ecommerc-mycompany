<?php

namespace App\Filament\Pages\Settings;

use App\Settings\MailSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Filament\Forms\Components\TextInput;
use App\Traits\SidebarTrait;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;

class ManageMail extends SettingsPage
{
    use SidebarTrait;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static string $settings = MailSettings::class;

    public static function getNavigationLabel(): string
    {
        return trans('admin.settings.mail.label');
    }

    public function getTitle(): string
    {
        return trans('admin.settings.mail.label');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testMail')
                ->label('Gửi Email Thử')
                ->color('info')
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    TextInput::make('receiver_email')
                        ->label('Email nhận thử')
                        ->email()
                        ->required()
                        ->default(auth()->user()->email),
                ])
                ->action(function (array $data) {
                    try {
                        $body = 'Chúc mừng sếp! SMTP đã hoạt động hoàn hảo trên hệ thống Antigravity.';

                        Mail::html($body, function ($message) use ($data) {
                            $message->to($data['receiver_email'])
                                ->subject('Kiểm tra kết nối SMTP - Antigravity Dashboard');
                        });

                        Notification::make()
                            ->title('Gửi thử thành công!')
                            ->success()
                            ->body('Vui lòng kiểm tra hộp thư của bạn.')
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gửi thử thất bại!')
                            ->danger()
                            ->body('Lỗi: ' . $e->getMessage())
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Cấu hình SMTP')
                    ->description('Cài đặt thông số máy chủ gửi email.')
                    ->aside()
                    ->schema([
                        TextInput::make('email_host')
                            ->label('SMTP host')
                            ->placeholder('smtp.example.com')
                            ->required(),
                        TextInput::make('email_port')
                            ->label('SMTP port')
                            ->placeholder('587')
                            ->numeric()
                            ->required(),
                        TextInput::make('email_encryption')
                            ->label('Encryption')
                            ->placeholder('tls/ssl')
                            ->required(),
                        TextInput::make('email_username')
                            ->label('Username')
                            ->placeholder('user@example.com')
                            ->required(),
                        TextInput::make('email_password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Cấu hình Người gửi')
                    ->description('Thông tin hiển thị khi khách hàng nhận được mail.')
                    ->aside()
                    ->schema([
                        TextInput::make('email_from_address')
                            ->label('From email')
                            ->email()
                            ->required(),
                        TextInput::make('email_from_name')
                            ->label('From name')
                            ->required(),
                    ]),
            ]);
    }
}
