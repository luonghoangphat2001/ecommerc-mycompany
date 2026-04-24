<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Filament\Forms\Components\Textarea;


class TextAreaBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('text_area')
            ->schema([
                Textarea::make('text_area')
                    ->label('Nội dung')
                    ->rows(5)
                    ->placeholder('Nhập nội dung...'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
