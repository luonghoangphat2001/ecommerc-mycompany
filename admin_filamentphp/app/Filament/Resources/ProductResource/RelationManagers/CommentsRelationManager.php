<?php



namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('admin.comment.label');
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(trans('admin.comment.label'))
                    ->required(),

                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label(trans('admin.comment.customer'))
                    ->searchable()
                    ->required(),

                Forms\Components\Toggle::make('is_visible')
                    ->label(trans('admin.is_visible'))
                    ->default(true),

                Forms\Components\MarkdownEditor::make('content')
                    ->label(trans('admin.comment.content'))
                    ->required(),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->schema([
                TextEntry::make('title')
                    ->label(trans('admin.comment.post.title')),
                TextEntry::make('user.name')
                    ->label(trans('admin.comment.customer')),
                IconEntry::make('is_visible')
                    ->label(trans('admin.is_visible')),
                TextEntry::make('content')
                    ->label(trans('admin.comment.content'))
                    ->markdown(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(trans('admin.comment.post.title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label(trans('admin.comment.customer'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_visible')
                    ->label(trans('admin.is_visible'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function ($record) {
                        /** @var User $user */
                        $user = auth()->user();

                        Notification::make()
                            ->title('New comment')
                            ->icon('heroicon-o-chat-bubble-bottom-center-text')
                            ->body("**{$record->user->name} commented on product ({$record->commentable->name}).**")
                            ->sendToDatabase($user);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
