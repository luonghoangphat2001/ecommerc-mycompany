<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Image;
use Filament\Forms\Components\FileUpload;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Awcodes\Curator\Components\Forms\CuratorPicker;

class ImageBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('image')
            ->schema([
                CuratorPicker::make('image')
                    ->label('Ảnh')
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
