<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Filament\Forms\Components\MarkdownEditor;

class CodeEditorBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('code_editor')
            ->schema([
                //
                MarkdownEditor::make('code_editor')
                    ->label('Nội dung')
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
