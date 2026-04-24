<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Awcodes\Curator\Components\Forms\CuratorPicker;

class GalleryBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('gallery')
            ->schema([
                CuratorPicker::make('gallery')
                    ->label('Album hình ảnh')
                    ->size('md')
                    ->multiple()
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
